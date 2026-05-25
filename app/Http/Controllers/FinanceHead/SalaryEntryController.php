<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\SalaryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalaryEntryController extends Controller
{
    public function update(Request $request, SalaryEntry $salaryEntry): JsonResponse
    {
        $data = $request->validate([
            'person_count' => 'required|integer|min:0',
            'atm_amount'   => 'required|numeric|min:0',
            'cash_amount'  => 'required|numeric|min:0',
            'annual_amount' => 'nullable|numeric|min:0',
            'remark'       => 'nullable|string|max:255',
        ]);

        $mode = $salaryEntry->budgetCode?->annual_mode ?? 'x12';

        $salaryEntry->person_count = (int) $data['person_count'];
        $salaryEntry->atm_amount   = (float) $data['atm_amount'];
        $salaryEntry->cash_amount  = (float) $data['cash_amount'];
        $salaryEntry->remark       = $data['remark'] ?? null;

        // For 'direct' mode, accept the annual_amount from the request
        if ($mode === 'direct' && isset($data['annual_amount'])) {
            $salaryEntry->annual_amount = (float) $data['annual_amount'];
        }

        $salaryEntry->save();

        return response()->json([
            'success'       => true,
            'monthly_total' => $salaryEntry->monthly_total,
            'annual_amount' => $salaryEntry->annual_amount,
        ]);
    }
}
