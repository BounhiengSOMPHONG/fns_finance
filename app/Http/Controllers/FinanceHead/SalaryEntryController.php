<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\SalaryEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SalaryEntryController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'plan_id' => 'required|integer|exists:salary_plans,id',
            'chart_of_account_id' => 'required|integer|exists:chart_of_accounts,id',
            'person_count' => 'nullable|integer|min:0',
            'payment_type' => 'required|in:cash,transfer',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $entry = SalaryEntry::create([
            'plan_id' => (int) $data['plan_id'],
            'chart_of_account_id' => (int) $data['chart_of_account_id'],
            'person_count' => (int) ($data['person_count'] ?? 0),
            'payment_type' => $data['payment_type'],
            'amount' => (float) ($data['amount'] ?? 0),
        ]);
        $entry->load('chartOfAccount');

        return response()->json([
            'success' => true,
            'entry' => $this->serialize($entry),
        ]);
    }

    public function update(Request $request, SalaryEntry $salaryEntry): JsonResponse
    {
        $data = $request->validate([
            'chart_of_account_id' => 'sometimes|required|integer|exists:chart_of_accounts,id',
            'person_count' => 'nullable|integer|min:0',
            'payment_type' => 'required|in:cash,transfer',
            'amount' => 'nullable|numeric|min:0',
        ]);

        if (array_key_exists('chart_of_account_id', $data)) {
            $salaryEntry->chart_of_account_id = (int) $data['chart_of_account_id'];
        }
        $salaryEntry->person_count = (int) ($data['person_count'] ?? 0);
        $salaryEntry->payment_type = $data['payment_type'];
        $salaryEntry->amount = (float) ($data['amount'] ?? 0);
        $salaryEntry->save();
        $salaryEntry->load('chartOfAccount');

        return response()->json([
            'success' => true,
            'entry' => $this->serialize($salaryEntry),
        ]);
    }

    public function destroy(SalaryEntry $salaryEntry): JsonResponse
    {
        $salaryEntry->delete();

        return response()->json(['success' => true]);
    }

    private function serialize(SalaryEntry $e): array
    {
        return [
            'id' => $e->id,
            'chart_of_account_id' => $e->chart_of_account_id,
            'account_code' => $e->chartOfAccount?->account_code,
            'account_name' => $e->chartOfAccount?->account_name,
            'person_count' => $e->person_count,
            'payment_type' => $e->payment_type,
            'amount' => $e->amount,
            'monthly_total' => $e->monthly_total,
            'annual_amount' => $e->annual_amount,
        ];
    }
}
