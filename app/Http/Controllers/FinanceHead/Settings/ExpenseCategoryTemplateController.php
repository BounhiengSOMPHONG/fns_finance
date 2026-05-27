<?php

namespace App\Http\Controllers\FinanceHead\Settings;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategoryTemplate;
use Illuminate\Http\Request;

class ExpenseCategoryTemplateController extends Controller
{
    public function index()
    {
        $templates = ExpenseCategoryTemplate::topLevel()
            ->with('children.children.children')
            ->get();

        return view('dashboards.finance_head.settings.expense-categories.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'parent_id'  => 'nullable|exists:expense_category_templates,id',
            'ref_code'   => 'required|string|max:20',
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        ExpenseCategoryTemplate::create([
            'parent_id'  => $data['parent_id'] ?? null,
            'ref_code'   => $data['ref_code'],
            'name'       => $data['name'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect()->route('head_of_finance.settings.expense-categories.index')
            ->with('success', 'ເພີ່ມໝວດສຳເລັດ');
    }

    public function update(Request $request, ExpenseCategoryTemplate $expenseCategoryTemplate)
    {
        $data = $request->validate([
            'ref_code'   => 'required|string|max:20',
            'name'       => 'required|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $expenseCategoryTemplate->update($data);

        return redirect()->route('head_of_finance.settings.expense-categories.index')
            ->with('success', 'ອັບເດດໝວດສຳເລັດ');
    }

    public function destroy(ExpenseCategoryTemplate $expenseCategoryTemplate)
    {
        $this->deleteRecursive($expenseCategoryTemplate);

        return redirect()->route('head_of_finance.settings.expense-categories.index')
            ->with('success', 'ລຶບໝວດສຳເລັດ');
    }

    private function deleteRecursive(ExpenseCategoryTemplate $template): void
    {
        foreach ($template->children()->get() as $child) {
            $this->deleteRecursive($child);
        }
        $template->delete();
    }
}
