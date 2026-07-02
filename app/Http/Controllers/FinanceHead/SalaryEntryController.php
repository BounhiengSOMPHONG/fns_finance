<?php

declare(strict_types=1);

namespace App\Http\Controllers\FinanceHead;

use App\Http\Controllers\Controller;
use App\Models\SalaryEntry;
use App\Models\SalaryPlan;
use Illuminate\Database\QueryException;
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
        $this->ensurePlanCanBeEdited((int) $data['plan_id']);

        $personCount = (int) ($data['person_count'] ?? 0);
        $amount = (float) ($data['amount'] ?? 0);

        if ($personCount === 0 && $amount === 0.0) {
            SalaryEntry::query()
                ->where('plan_id', (int) $data['plan_id'])
                ->where('chart_of_account_id', (int) $data['chart_of_account_id'])
                ->delete();

            return response()->json([
                'success' => true,
                'deleted' => true,
                'entry' => null,
            ]);
        }

        $entry = SalaryEntry::query()->firstOrNew([
            'plan_id' => (int) $data['plan_id'],
            'chart_of_account_id' => (int) $data['chart_of_account_id'],
        ]);

        $entry->person_count = $personCount;
        $entry->payment_type = $data['payment_type'];
        $entry->amount = $amount;

        try {
            $entry->save();
        } catch (QueryException $exception) {
            if ($exception->getCode() !== '23000') {
                throw $exception;
            }

            $entry = SalaryEntry::query()
                ->where('plan_id', (int) $data['plan_id'])
                ->where('chart_of_account_id', (int) $data['chart_of_account_id'])
                ->firstOrFail();

            $entry->person_count = $personCount;
            $entry->payment_type = $data['payment_type'];
            $entry->amount = $amount;
            $entry->save();
        }

        $entry->load('chartOfAccount');

        return response()->json([
            'success' => true,
            'entry' => $this->serialize($entry),
        ]);
    }

    public function update(Request $request, SalaryEntry $salaryEntry): JsonResponse
    {
        $this->ensurePlanCanBeEdited((int) $salaryEntry->plan_id);

        $data = $request->validate([
            'chart_of_account_id' => 'sometimes|required|integer|exists:chart_of_accounts,id',
            'person_count' => 'nullable|integer|min:0',
            'payment_type' => 'required|in:cash,transfer',
            'amount' => 'nullable|numeric|min:0',
        ]);
        $personCount = (int) ($data['person_count'] ?? 0);
        $amount = (float) ($data['amount'] ?? 0);

        if ($personCount === 0 && $amount === 0.0) {
            $salaryEntry->delete();

            return response()->json([
                'success' => true,
                'deleted' => true,
                'entry' => null,
            ]);
        }

        if (array_key_exists('chart_of_account_id', $data)) {
            $salaryEntry->chart_of_account_id = (int) $data['chart_of_account_id'];
        }
        $salaryEntry->person_count = $personCount;
        $salaryEntry->payment_type = $data['payment_type'];
        $salaryEntry->amount = $amount;
        $salaryEntry->save();
        $salaryEntry->load('chartOfAccount');

        return response()->json([
            'success' => true,
            'entry' => $this->serialize($salaryEntry),
        ]);
    }

    public function destroy(SalaryEntry $salaryEntry): JsonResponse
    {
        $this->ensurePlanCanBeEdited((int) $salaryEntry->plan_id);

        $salaryEntry->delete();

        return response()->json(['success' => true]);
    }

    private function ensurePlanCanBeEdited(int $salaryPlanId): void
    {
        $salaryPlan = SalaryPlan::with('planningYear')->findOrFail($salaryPlanId);

        abort_if(
            $salaryPlan->planningYear?->canBeEdited() === false,
            423,
            'ແຜນນີ້ຢູ່ໃນສະຖານະຂໍຄວາມເຫັນ ບໍ່ສາມາດແກ້ໄຂໄດ້'
        );
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
