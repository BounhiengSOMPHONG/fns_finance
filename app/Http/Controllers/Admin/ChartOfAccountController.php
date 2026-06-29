<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChartOfAccountController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ChartOfAccount::with('parent');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        $chartOfAccounts = $query->orderBy('account_code')->paginate(10)->withQueryString();
        $parentAccounts = ChartOfAccount::orderBy('account_code')->get(['id', 'account_code', 'account_name', 'parent_id']);

        return view('dashboards.admin.chart-of-accounts.index', compact('chartOfAccounts', 'parentAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentAccounts = ChartOfAccount::orderBy('account_code')->get(['id', 'account_code', 'account_name']);

        return view('dashboards.admin.chart-of-accounts.create', compact('parentAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?? null;

        ChartOfAccount::create($validated);

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'ສ້າງບັນຊີສຳເລັດ');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->load('parent');

        return view('dashboards.admin.chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        $parentAccounts = ChartOfAccount::whereKeyNot($chartOfAccount->id)
            ->orderBy('account_code')
            ->get(['id', 'account_code', 'account_name']);

        return view('dashboards.admin.chart-of-accounts.edit', compact('chartOfAccount', 'parentAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:20', Rule::unique('chart_of_accounts')->ignore($chartOfAccount->id)],
            'account_name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'integer', 'exists:chart_of_accounts,id', Rule::notIn([$chartOfAccount->id])],
        ]);

        $validated['parent_id'] = $validated['parent_id'] ?? null;

        if ($validated['parent_id'] && $this->parentWouldCreateCycle($chartOfAccount, (int) $validated['parent_id'])) {
            return back()
                ->withErrors(['parent_id' => 'ບໍ່ສາມາດເລືອກບັນຊີລູກເປັນບັນຊີແມ່ໄດ້'])
                ->withInput();
        }

        $chartOfAccount->update($validated);

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'ອັບເດດບັນຊີສຳເລັດ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'ລຶບບັນຊີສຳເລັດ');
    }

    private function parentWouldCreateCycle(ChartOfAccount $account, int $parentId): bool
    {
        $seen = [];

        while ($parentId) {
            if ($parentId === $account->id || in_array($parentId, $seen, true)) {
                return true;
            }

            $seen[] = $parentId;
            $parentId = (int) (ChartOfAccount::whereKey($parentId)->value('parent_id') ?? 0);
        }

        return false;
    }
}
