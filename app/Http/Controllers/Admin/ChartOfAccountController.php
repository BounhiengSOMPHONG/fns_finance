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
        $query = ChartOfAccount::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                    ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        $chartOfAccounts = $query->orderBy('account_code')->paginate(10)->withQueryString();

        return view('admin.chart-of-accounts.index', compact('chartOfAccounts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.chart-of-accounts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts',
            'account_name' => 'required|string|max:255',
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
        return view('admin.chart-of-accounts.edit', compact('chartOfAccount'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ChartOfAccount $chartOfAccount)
    {
        $validated = $request->validate([
            'account_code' => ['required', 'string', 'max:20', Rule::unique('chart_of_accounts')->ignore($chartOfAccount->id)],
            'account_name' => 'required|string|max:255',
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
}
