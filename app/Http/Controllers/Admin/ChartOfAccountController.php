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

        // Filter by parent_id
        if ($request->has('parent_id') && strlen($request->parent_id) > 0) {
            $query->where('parent_id', (int) $request->parent_id);
        }

        $perPage = request('per_page', 25);
        if ($perPage === 'all') {
            $perPage = $query->count() > 0 ? $query->count() : 1;
        } else {
            $perPage = (int) $perPage;
        }
        $chartOfAccounts = $query->orderBy('account_code')->paginate($perPage)->withQueryString();
        $parentAccounts = ChartOfAccount::has('children')->orderBy('account_code')->get();

        return view('admin.chart-of-accounts.index', compact('chartOfAccounts', 'parentAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $chartOfAccounts = ChartOfAccount::orderBy('account_code')->get();
        return view('admin.chart-of-accounts.create', compact('chartOfAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
        ]);

        ChartOfAccount::create($validated);

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'สร้างบัญชีสำเร็จ');
    }

    /**
     * Display the specified resource.
     */
    public function show(ChartOfAccount $chartOfAccount)
    {
        return view('admin.chart-of-accounts.show', compact('chartOfAccount'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ChartOfAccount $chartOfAccount)
    {
        $allAccounts = ChartOfAccount::orderBy('account_code')->get();
        return view('admin.chart-of-accounts.edit', compact('chartOfAccount', 'allAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:20', Rule::unique('chart_of_accounts')->ignore($chartOfAccount->id)],
            'account_name' => 'required|string|max:255',
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id', Rule::notIn([$chartOfAccount->id])],
        ]);

        $chartOfAccount->update($validated);

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'อัปเดตบัญชีสำเร็จ');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ChartOfAccount $chartOfAccount)
    {
        $chartOfAccount->delete();

        return redirect()
            ->route('admin.chart-of-accounts.index')
            ->with('success', 'ลบบัญชีสำเร็จ');
    }
    /**
     * Remove the specified resources from storage.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:chart_of_accounts,id',
        ]);

        ChartOfAccount::whereIn('id', $request->ids)->delete();

        return redirect()->route('admin.chart-of-accounts.index')->with('success', 'ລຶບບັນຊີທີ່ເລືອກສຳເລັດ');
    }
}
