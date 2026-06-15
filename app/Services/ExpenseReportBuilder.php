<?php

namespace App\Services;

use App\Models\ExpensePatternField;
use App\Models\ExpensePlan;
use App\Models\ExpenseSection;
use App\Models\ExpenseSubsection;
use App\Models\PlanningYear;
use Illuminate\Support\Collection;

class ExpenseReportBuilder
{
    private const SECTION_CODES = ['2.1', '2.2', '2.3', '2.4', '2.5', '2.6'];

    public function buildForPlanningYear(PlanningYear $planningYear): array
    {
        $sections = ExpenseSection::with([
            'subsections.defaultPattern.fields',
            'subsections.children.defaultPattern.fields',
        ])
            ->where('planning_year_id', $planningYear->id)
            ->get()
            ->sortBy(fn (ExpenseSection $section) => $this->codeSortKey($section->code))
            ->values();

        $plans = ExpensePlan::with(['pattern.fields', 'values', 'section', 'subsection.defaultPattern.fields'])
            ->where('planning_year_id', $planningYear->id)
            ->get()
            ->sortBy(fn (ExpensePlan $plan) => sprintf(
                '%s|%s|%08d',
                $this->codeSortKey($plan->section?->code ?? ''),
                $this->codeSortKey($plan->subsection?->code ?? ''),
                $plan->id
            ))
            ->values();

        $sectionsByCode = $sections->keyBy('code');
        $subsections = $sections->flatMap(fn (ExpenseSection $section) => $section->subsections)->values();
        $subsectionsByCode = $subsections->keyBy('code');
        $plansBySection = $plans->groupBy(fn (ExpensePlan $plan) => $plan->section?->code ?: '');
        $plansBySubsection = $plans->groupBy(fn (ExpensePlan $plan) => $plan->subsection?->code ?: '');

        $reportSectionCodes = collect(self::SECTION_CODES)
            ->merge($sections->pluck('code')->filter())
            ->unique()
            ->sortBy(fn (string $code) => $this->codeSortKey($code))
            ->values();

        $reportSections = $reportSectionCodes
            ->map(function (string $code, int $index) use ($sectionsByCode, $subsectionsByCode, $plansBySection, $plansBySubsection): array {
                $section = $sectionsByCode->get($code);
                $sectionPlans = $plansBySection->get($code, collect());
                $directSubsections = $this->directSubsections($subsectionsByCode, $code);

                return $this->buildSection(
                    $code,
                    $index + 1,
                    $section,
                    $sectionPlans,
                    $directSubsections,
                    $subsectionsByCode,
                    $plansBySubsection
                );
            })
            ->values();

        return [
            'sections' => $reportSections,
            'total' => (float) $reportSections->sum('total'),
            'periodTotal' => (float) $reportSections->sum('period_total'),
        ];
    }

    private function buildSection(
        string $code,
        int $number,
        ?ExpenseSection $section,
        Collection $sectionPlans,
        Collection $directSubsections,
        Collection $subsectionsByCode,
        Collection $plansBySubsection
    ): array {
        $detailGroups = $directSubsections
            ->map(fn (ExpenseSubsection $subsection) => $this->buildDetailGroup($subsection, $subsectionsByCode, $plansBySubsection))
            ->values();

        $total = (float) $sectionPlans->sum(fn (ExpensePlan $plan) => $this->yearlyTotal($plan));
        $periodCount = (float) ($section?->summary_period_count ?? 12);
        $periodTotal = $periodCount > 0 ? $total / $periodCount : 0.0;

        return [
            'number' => $number,
            'code' => $code,
            'title' => $section?->name ?: 'ລາຍຈ່າຍ ' . $code,
            'period_count' => $periodCount,
            'period_total' => $periodTotal,
            'total' => $total,
            'note' => $this->firstNote($sectionPlans),
            'details' => $detailGroups,
        ];
    }

    private function buildDetailGroup(ExpenseSubsection $subsection, Collection $subsectionsByCode, Collection $plansBySubsection): array
    {
        $subsectionCodes = $this->subtreeCodes($subsectionsByCode, $subsection->code);
        $plans = $subsectionCodes
            ->flatMap(fn (string $code) => $plansBySubsection->get($code, collect()))
            ->sortBy(fn (ExpensePlan $plan) => sprintf('%s|%08d', $this->codeSortKey($plan->subsection?->code ?? ''), $plan->id))
            ->values();

        $pattern = $plans->first()?->pattern ?? $subsection->defaultPattern;
        $columns = $this->columnsFor($pattern?->fields ?? collect());
        $rows = $plans
            ->map(fn (ExpensePlan $plan, int $index) => $this->buildRow($plan, $columns, $index + 1))
            ->values();
        $total = (float) $plans->sum(fn (ExpensePlan $plan) => $this->yearlyTotal($plan));

        return [
            'code' => $subsection->code,
            'title' => $subsection->name ?: 'ລາຍການ ' . $subsection->code,
            'columns' => $columns,
            'rows' => $rows,
            'total' => $total,
        ];
    }

    private function buildRow(ExpensePlan $plan, array $columns, int $number): array
    {
        $values = $this->valuesByKey($plan);
        $total = $this->yearlyTotal($plan, $values);

        return [
            'number' => $number,
            'item_name' => $this->textValue($values, 'item_name') ?: $plan->plan_detail,
            'reference' => $this->textValue($values, 'reference'),
            'note' => $this->textValue($values, 'note') ?: $plan->detail,
            'total' => $total,
            'values' => collect($columns)
                ->mapWithKeys(fn (array $column) => [$column['key'] => $this->value($values, $column['key'])])
                ->all(),
        ];
    }

    private function columnsFor(Collection $fields): array
    {
        return $fields
            ->reject(fn (ExpensePatternField $field) => in_array($field->field_key, ['item_name', 'reference', 'note', 'yearly_total'], true))
            ->map(fn (ExpensePatternField $field) => [
                'key' => $field->field_key,
                'label' => $this->fieldLabel($field->field_key, $field->default_label),
                'type' => $field->data_type,
            ])
            ->values()
            ->all();
    }

    private function directSubsections(Collection $subsectionsByCode, string $sectionCode): Collection
    {
        return $subsectionsByCode
            ->filter(fn (ExpenseSubsection $subsection) => str_starts_with($subsection->code, $sectionCode . '.')
                && $this->parentCode($subsection->code) === $sectionCode)
            ->sortBy(fn (ExpenseSubsection $subsection) => $this->codeSortKey($subsection->code))
            ->values();
    }

    private function subtreeCodes(Collection $subsectionsByCode, string $rootCode): Collection
    {
        return $subsectionsByCode
            ->keys()
            ->filter(fn (string $code) => $code === $rootCode || str_starts_with($code, $rootCode . '.'))
            ->sortBy(fn (string $code) => $this->codeSortKey($code))
            ->values();
    }

    private function valuesByKey(ExpensePlan $plan): array
    {
        return $plan->values
            ->mapWithKeys(fn ($value) => [$value->field_key => $this->valueFromRecord($value)])
            ->all();
    }

    private function valueFromRecord($value): mixed
    {
        return $value->value_text
            ?? $value->value_number
            ?? $value->value_date
            ?? $value->value_boolean;
    }

    private function value(array $values, string $key): mixed
    {
        return $values[$key] ?? null;
    }

    private function textValue(array $values, string $key): ?string
    {
        $value = $this->value($values, $key);

        return $value === null || $value === '' ? null : (string) $value;
    }

    private function yearlyTotal(ExpensePlan $plan, ?array $values = null): float
    {
        $total = (float) ($plan->value('yearly_total')?->value_number ?? 0);

        if ($total > 0) {
            return $total;
        }

        return $this->calculatedTotal($plan, $values ?? $this->valuesByKey($plan));
    }

    private function calculatedTotal(ExpensePlan $plan, array $values): float
    {
        $number = fn (string $key): float => (float) ($values[$key] ?? 0);

        return match ($plan->pattern?->key) {
            'monthly' => $number('amount_per_month') * $number('month_count'),
            'unit_quantity' => $number('unit_price') * $number('quantity'),
            'unit_quantity_frequency' => $number('unit_price') * $number('quantity') * $number('times_per_year'),
            'frequency_based' => $number('unit_price') * $number('quantity') * $number('frequency_count'),
            'event_based' => $number('unit_price') * $number('event_count') * $number('people_count'),
            default => 0.0,
        };
    }

    private function firstNote(Collection $plans): ?string
    {
        foreach ($plans as $plan) {
            $values = $this->valuesByKey($plan);
            $note = $this->textValue($values, 'note') ?: $plan->detail;

            if ($note) {
                return $note;
            }
        }

        return null;
    }

    private function fieldLabel(string $key, ?string $defaultLabel): string
    {
        if ($defaultLabel) {
            return $defaultLabel;
        }

        return [
            'amount_per_month' => 'ຕໍ່ເດືອນ',
            'month_count' => 'ຈ/ນເດືອນ',
            'unit_price' => 'ລາຄາຕໍ່ໜ່ວຍ',
            'quantity' => 'ຈຳນວນ',
            'times_per_year' => 'ຈຳນວນຄັ້ງ',
            'frequency_count' => 'ຈຳນວນເດືອນ/ຄັ້ງ',
            'event_count' => 'ຈຳນວນຄັ້ງ',
            'people_count' => 'ຈຳນວນຄົນ',
        ][$key] ?? str_replace('_', ' ', $key);
    }

    private function parentCode(string $code): string
    {
        $parts = explode('.', $code);
        array_pop($parts);

        return implode('.', $parts);
    }

    private function codeSortKey(string $code): string
    {
        return collect(explode('.', $code))
            ->map(fn (string $part) => str_pad($part, 4, '0', STR_PAD_LEFT))
            ->implode('.');
    }
}
