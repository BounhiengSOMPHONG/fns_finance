<?php

namespace App\Http\Controllers\HeadOfFinance;

use App\Http\Controllers\Controller;
use App\Models\BudgetLineItem;
use App\Models\BudgetPlan;
use App\Models\BudgetPlanComment;
use App\Models\ChartOfAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AnnualBudgetPlanController extends Controller
{
    // ─── Plans CRUD ───────────────────────────────────────────────────────

    public function index()
    {
        $plans = BudgetPlan::orderByDesc('fiscal_year')->get();
        return view('head_of_finance.annual-budget.index', compact('plans'));
    }

    public function create()
    {
        return view('head_of_finance.annual-budget.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100|unique:budget_plans,fiscal_year',
        ]);

        BudgetPlan::create([
            'fiscal_year' => $request->fiscal_year,
            'status' => 'DRAFT',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('head_of_finance.annual-budget.index')
            ->with('success', 'ສ້າງແຜນງົບປະມານສຳເລັດ!');
    }

    public function show(BudgetPlan $annualBudget)
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        $annualBudget->load(['lineItems.account', 'lineItems.periodAllocations', 'comments.user.role', 'comments.markedBy']);
        $annualBudget->setRelation('lineItems', $this->sortLineItemsHierarchically($annualBudget->lineItems));
        return view('head_of_finance.annual-budget.show', compact('annualBudget', 'accounts'));
    }

    public function preview(BudgetPlan $annualBudget)
    {
        $annualBudget->load(['lineItems.account']);
        $annualBudget->setRelation('lineItems', $this->sortLineItemsHierarchically($annualBudget->lineItems));
        return view('head_of_finance.annual-budget.preview', compact('annualBudget'));
    }

    public function exportPdf(BudgetPlan $annualBudget)
    {
        $annualBudget->load(['lineItems.account', 'lineItems.periodAllocations']);
        $annualBudget->setRelation('lineItems', $this->sortLineItemsHierarchically($annualBudget->lineItems));

        $html = view('head_of_finance.annual-budget.pdf', compact('annualBudget'))->render();

        $defaultConfig = (new \Mpdf\Config\ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new \Mpdf\Config\FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4-L',
            'margin_left' => 15,
            'margin_right' => 15,
            'margin_top' => 16,
            'margin_bottom' => 16,
            'fontDir' => array_merge($fontDirs, [
                storage_path('fonts'),
            ]),
            'fontdata' => $fontData + [
                'notosanslao' => [
                    'R' => 'NotoSansLao-Regular.ttf',
                    'B' => 'NotoSansLao-Bold.ttf',
                ],
            ],
            'default_font' => 'notosanslao',
        ]);

        // Fix mPDF complex script issues for Lao
        $mpdf->autoScriptToLang = true;
        $mpdf->autoLangToFont = true;

        $mpdf->WriteHTML($html);
        return response($mpdf->Output('', 'S'))
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="ແຜນງົບປະມານປະຈຳປີ_' . $annualBudget->fiscal_year . '.pdf"');
    }

    public function edit(BudgetPlan $annualBudget)
    {
        $accounts = ChartOfAccount::orderBy('account_code')->get();
        $annualBudget->load(['lineItems.account']);
        return view('head_of_finance.annual-budget.edit', compact('annualBudget', 'accounts'));
    }

    public function update(Request $request, BudgetPlan $annualBudget)
    {
        $request->validate([
            'fiscal_year' => 'required|integer|min:2000|max:2100|unique:budget_plans,fiscal_year,' . $annualBudget->id,
        ]);

        $annualBudget->update($request->only('fiscal_year'));

        return redirect()->route('head_of_finance.annual-budget.show', $annualBudget)
            ->with('success', 'ອັບເດດແຜນງົບປະມານສຳເລັດ!');
    }

    public function destroy(BudgetPlan $annualBudget)
    {
        $annualBudget->lineItems()->each(function ($item) {
            $item->periodAllocations()->delete();
        });
        $annualBudget->lineItems()->delete();
        $annualBudget->delete();

        return redirect()->route('head_of_finance.annual-budget.index')
            ->with('success', 'ລຶບແຜນງົບປະມານສຳເລັດ!');
    }

    public function submit(BudgetPlan $annualBudget)
    {
        $allowed = ['DRAFT', 'MODIFYING'];
        if (!in_array(strtoupper($annualBudget->status), $allowed)) {
            return back()->with('error', 'ສະຖານະບໍ່ຖືກຕ້ອງ');
        }

        $annualBudget->update([
            'status' => 'PENDING_REVIEW',
            'submission_round' => $annualBudget->submission_round + 1,
        ]);

        return back()->with('success', 'ສົ່ງແຜນງົບປະມານສຳເລັດ!');
    }

    public function unsubmit(BudgetPlan $annualBudget)
    {
        if (strtoupper($annualBudget->status) !== 'PENDING_REVIEW') {
            return back()->with('error', 'ສາມາດຍົກເລີກການສົ່ງໄດ້ສະເພາະແຜນທີ່ກຳລັງລໍຖ້າກວດສອບເທົ່ານັ້ນ');
        }

        $annualBudget->update(['status' => 'MODIFYING']);

        return back()->with('success', 'ຍົກເລີກການສົ່ງສຳເລັດ! ທ່ານສາມາດແກ້ໄຂແຜນໄດ້ແລ້ວ.');
    }

    public function markComment(BudgetPlan $annualBudget, BudgetPlanComment $comment)
    {
        if ($comment->budget_plan_id !== $annualBudget->id) {
            return response()->json(['error' => 'ຂໍ້ມູນບໍ່ຖືກຕ້ອງ'], 403);
        }

        if ($comment->isMarked()) {
            $comment->update(['marked_at' => null, 'marked_by' => null]);
            return response()->json([
                'marked'    => false,
                'markedBy'  => null,
                'markedAt'  => null,
            ]);
        } else {
            $comment->update([
                'marked_at' => now(),
                'marked_by' => auth()->id(),
            ]);
            $comment->load('markedBy');
            return response()->json([
                'marked'   => true,
                'markedBy' => $comment->markedBy->full_name ?? 'HoF',
                'markedAt' => $comment->marked_at->format('d/m/Y H:i'),
            ]);
        }
    }

    // ─── Line Item CRUD ───────────────────────────────────────────────────

    public function storeItem(Request $request, BudgetPlan $annualBudget)
    {
        $request->validate([
            'account_id' => 'required|exists:chart_of_accounts,id',
            'amount_regular' => 'nullable|numeric|min:0',
            'amount_academic' => 'nullable|numeric|min:0',
        ]);

        try {
            $account = \App\Models\ChartOfAccount::findOrFail($request->account_id);
            if ($account->children()->exists()) {
                throw ValidationException::withMessages(['account_id' => 'ບໍ່ສາມາດບັນທຶກໃນໝວດຫຼັກໄດ້, ກະລຸນາເລືອກໝວดย่อยທີ່ສຸດ']);
            }

            DB::transaction(function () use ($request, $annualBudget) {
                // Prevent duplicate account in same plan
                $exists = $annualBudget->lineItems()->where('account_id', $request->account_id)->exists();
                if ($exists) {
                    throw ValidationException::withMessages(['account_id' => 'ບັນຊີນີ້ມີຢູ່ໃນແຜນແລ້ວ!']);
                }

                $annualBudget->lineItems()->create([
                    'account_id' => $request->account_id,
                    'amount_regular' => $request->amount_regular ?? 0,
                    'amount_academic' => $request->amount_academic ?? 0,
                ]);
            });

            return back()->with('success', 'ເພີ່ມລາຍການງົບປະມານສຳເລັດ!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function storeBulkItems(Request $request, BudgetPlan $annualBudget)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.account_id' => 'nullable|exists:chart_of_accounts,id',
            'items.*.amount_regular' => 'nullable|numeric|min:0',
            'items.*.amount_academic' => 'nullable|numeric|min:0',
        ]);

        $skipped = 0;
        $added = 0;

        try {
            DB::transaction(function () use ($request, $annualBudget, &$skipped, &$added) {
                $accounts = \App\Models\ChartOfAccount::with('children')->get()->keyBy('id');

                foreach ($request->items as $row) {
                    if (empty($row['account_id'])) continue;
                    
                    $acc = $accounts->get($row['account_id']);
                    if ($acc && $acc->children->count() > 0) {
                        continue; // Skip parent accounts
                    }

                    $exists = $annualBudget->lineItems()
                        ->where('account_id', $row['account_id'])
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $annualBudget->lineItems()->create([
                        'account_id' => $row['account_id'],
                        'amount_regular' => $row['amount_regular'] ?? 0,
                        'amount_academic' => $row['amount_academic'] ?? 0,
                    ]);
                    $added++;
                }
            });

            $msg = "ເພີ່ມ {$added} ລາຍການສຳເລັດ!";
            if ($skipped > 0) $msg .= " (ຂ້າມ {$skipped} ລາຍການທີ່ຊ້ຳ)";
            return back()->with('success', $msg);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function updateItem(Request $request, BudgetPlan $annualBudget, BudgetLineItem $item)
    {
        $request->validate([
            'amount_regular' => 'nullable|numeric|min:0',
            'amount_academic' => 'nullable|numeric|min:0',
        ]);

        try {
            $account = $item->account;
            if ($account->children()->exists()) {
                throw ValidationException::withMessages(['account_id' => 'ບໍ່ສາມາດແກ້ໄຂໝວດຫຼັກໄດ້']);
            }

            DB::transaction(function () use ($request, $annualBudget, $item) {
                $item->update([
                    'amount_regular' => $request->amount_regular ?? 0,
                    'amount_academic' => $request->amount_academic ?? 0,
                ]);
            });

            return back()->with('success', 'ອັບເດດລາຍການສຳເລັດ!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function destroyItem(BudgetPlan $annualBudget, BudgetLineItem $item)
    {
        $item->periodAllocations()->delete();
        $item->delete();

        return back()->with('success', 'ລຶບລາຍການສຳເລັດ!');
    }

    protected function synthesizeTreeAndRollUp($lineItems)
    {
        $allAccounts = \App\Models\ChartOfAccount::orderBy('account_code')->get();
        $accountMap = $allAccounts->keyBy('id');
        
        $childrenMap = [];
        foreach ($allAccounts as $acc) {
            if ($acc->parent_id) {
                $childrenMap[$acc->parent_id][] = $acc->id;
            }
        }
        
        $aggregated = [];
        foreach ($lineItems as $item) {
            $aggregated[$item->account_id] = [
                'amount_regular' => $item->amount_regular,
                'amount_academic' => $item->amount_academic,
                'original_item' => $item,
            ];
        }
        
        $computeSum = function($accountId) use (&$computeSum, &$aggregated, $childrenMap) {
            $reg = $aggregated[$accountId]['amount_regular'] ?? 0;
            $acad = $aggregated[$accountId]['amount_academic'] ?? 0;
            $hasItems = isset($aggregated[$accountId]['original_item']);
            
            if (isset($childrenMap[$accountId])) {
                $reg = 0; $acad = 0; // Parent's own DB amount is overridden by children sum
                foreach ($childrenMap[$accountId] as $childId) {
                    $childSums = $computeSum($childId);
                    $reg += $childSums['reg'];
                    $acad += $childSums['acad'];
                    if ($childSums['hasItems']) {
                        $hasItems = true;
                    }
                }
                $aggregated[$accountId]['amount_regular'] = $reg;
                $aggregated[$accountId]['amount_academic'] = $acad;
            }
            
            $aggregated[$accountId]['should_render'] = $hasItems || $reg > 0 || $acad > 0;
            return ['reg' => $reg, 'acad' => $acad, 'hasItems' => $hasItems];
        };
        
        $roots = $allAccounts->whereNull('parent_id');
        foreach ($roots as $root) {
            $computeSum($root->id);
        }
        
        $syntheticItems = collect();
        foreach ($allAccounts as $acc) {
            $shouldRender = $aggregated[$acc->id]['should_render'] ?? false;
            
            if ($shouldRender) {
                $reg = $aggregated[$acc->id]['amount_regular'] ?? 0;
                $acad = $aggregated[$acc->id]['amount_academic'] ?? 0;
                $isParent = isset($childrenMap[$acc->id]);
                
                if (isset($aggregated[$acc->id]['original_item'])) {
                    $item = $aggregated[$acc->id]['original_item'];
                    $item->amount_regular = $reg;
                    $item->amount_academic = $acad;
                    $item->is_parent = $isParent;
                    $item->setRelation('account', $acc);
                    $syntheticItems->push($item);
                } else {
                    $syntheticItem = new \App\Models\BudgetLineItem([
                        'account_id' => $acc->id,
                        'amount_regular' => $reg,
                        'amount_academic' => $acad,
                    ]);
                    $syntheticItem->is_parent = $isParent;
                    $syntheticItem->setRelation('account', $acc);
                    $syntheticItems->push($syntheticItem);
                }
            }
        }
        
        return $syntheticItems;
    }

    protected function sortLineItemsHierarchically($lineItems)
    {
        return $this->synthesizeTreeAndRollUp($lineItems);
    }
}
