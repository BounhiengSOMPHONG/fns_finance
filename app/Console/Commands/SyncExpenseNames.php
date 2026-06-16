<?php

namespace App\Console\Commands;

use App\Support\ExpenseStructureNames;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncExpenseNames extends Command
{
    protected $signature = 'expense:sync-names {--dry-run : Preview changes without writing to the database}';

    protected $description = 'Safely sync expense section, subsection, and guarded default row names.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $changes = $this->buildChanges();

        $this->renderChanges($changes, $dryRun);

        if ($dryRun) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($changes): void {
            $now = now();

            foreach ($changes['sections'] as $change) {
                DB::table('expense_sections')
                    ->where('id', $change['id'])
                    ->update(['name' => $change['new'], 'updated_at' => $now]);
            }

            foreach ($changes['subsections'] as $change) {
                DB::table('expense_subsections')
                    ->where('id', $change['id'])
                    ->update(['name' => $change['new'], 'updated_at' => $now]);
            }

            foreach ($changes['default_rows'] as $change) {
                DB::table('expense_subsection_default_rows')
                    ->where('id', $change['id'])
                    ->update(['item_name' => $change['new'], 'updated_at' => $now]);
            }

            foreach ($changes['plan_details'] as $change) {
                DB::table('expense_plans')
                    ->where('id', $change['id'])
                    ->update(['plan_detail' => $change['new'], 'updated_at' => $now]);
            }

            foreach ($changes['plan_value_item_names'] as $change) {
                DB::table('expense_plan_values')
                    ->where('id', $change['id'])
                    ->update(['value_text' => $change['new'], 'updated_at' => $now]);
            }
        });

        $this->info('Expense names synced.');

        return self::SUCCESS;
    }

    private function buildChanges(): array
    {
        $names = ExpenseStructureNames::names();

        $changes = [
            'sections' => [],
            'subsections' => [],
            'default_rows' => [],
            'plan_details' => [],
            'plan_value_item_names' => [],
        ];

        DB::table('expense_sections')
            ->whereIn('code', array_keys($names))
            ->orderBy('planning_year_id')
            ->orderBy('code')
            ->get(['id', 'planning_year_id', 'code', 'name'])
            ->each(function ($section) use (&$changes, $names): void {
                $target = $names[$section->code] ?? null;
                if ($target !== null && $section->name !== $target) {
                    $changes['sections'][] = [
                        'id' => $section->id,
                        'context' => 'year '.$section->planning_year_id,
                        'code' => $section->code,
                        'old' => $section->name,
                        'new' => $target,
                    ];
                }
            });

        DB::table('expense_subsections')
            ->whereIn('code', array_keys($names))
            ->orderBy('section_id')
            ->orderBy('code')
            ->get(['id', 'section_id', 'code', 'name'])
            ->each(function ($subsection) use (&$changes, $names): void {
                $target = $names[$subsection->code] ?? null;
                if ($target !== null && $subsection->name !== $target) {
                    $changes['subsections'][] = [
                        'id' => $subsection->id,
                        'context' => 'section '.$subsection->section_id,
                        'code' => $subsection->code,
                        'old' => $subsection->name,
                        'new' => $target,
                    ];
                }
            });

        $finalDefaultRows = [];

        DB::table('expense_subsection_default_rows')
            ->whereIn('subsection_code', array_keys($names))
            ->orderBy('subsection_code')
            ->orderBy('sort_order')
            ->get(['id', 'subsection_code', 'item_name', 'sort_order'])
            ->each(function ($row) use (&$changes, &$finalDefaultRows): void {
                $sortOrder = (int) $row->sort_order;
                $target = ExpenseStructureNames::defaultRowTargetName($row->subsection_code, $sortOrder, $row->item_name);
                $finalName = $target ?? $row->item_name;

                $finalDefaultRows[$row->subsection_code][$sortOrder] = [
                    'current' => $row->item_name,
                    'final' => $finalName,
                    'old_candidates' => array_values(array_unique(array_filter([
                        $row->item_name,
                        ...ExpenseStructureNames::knownDefaultRowTypos($row->subsection_code, $sortOrder),
                    ], fn ($value) => trim((string) $value) !== ''))),
                ];

                if ($target !== null && $target !== $row->item_name) {
                    $changes['default_rows'][] = [
                        'id' => $row->id,
                        'context' => 'sort '.$sortOrder,
                        'code' => $row->subsection_code,
                        'old' => $row->item_name,
                        'new' => $target,
                    ];
                }
            });

        foreach ($finalDefaultRows as $code => $rowsBySortOrder) {
            foreach ($rowsBySortOrder as $sortOrder => $rowInfo) {
                $candidates = $this->guardedItemNameCandidates($code, (int) $sortOrder, $rowInfo['old_candidates']);
                if ($candidates === []) {
                    continue;
                }

                $plans = DB::table('expense_plans')
                    ->join('expense_subsections', 'expense_subsections.id', '=', 'expense_plans.subsection_id')
                    ->where('expense_subsections.code', $code)
                    ->whereIn('expense_plans.plan_detail', $candidates)
                    ->orderBy('expense_plans.id')
                    ->get([
                        'expense_plans.id',
                        'expense_plans.plan_detail',
                        'expense_plans.subsection_id',
                    ]);

                foreach ($plans as $plan) {
                    if ($plan->plan_detail === $rowInfo['final']) {
                        continue;
                    }

                    $changes['plan_details'][] = [
                        'id' => $plan->id,
                        'context' => 'subsection '.$plan->subsection_id,
                        'code' => $code,
                        'old' => $plan->plan_detail,
                        'new' => $rowInfo['final'],
                    ];

                    DB::table('expense_plan_values')
                        ->where('expense_plan_id', $plan->id)
                        ->where('field_key', 'item_name')
                        ->where(function ($query) use ($candidates): void {
                            $query->whereIn('value_text', $candidates)
                                ->orWhereNull('value_text')
                                ->orWhere('value_text', '');
                        })
                        ->orderBy('id')
                        ->get(['id', 'expense_plan_id', 'value_text'])
                        ->each(function ($value) use (&$changes, $code, $rowInfo): void {
                            if ($value->value_text === $rowInfo['final']) {
                                return;
                            }

                            $changes['plan_value_item_names'][] = [
                                'id' => $value->id,
                                'context' => 'plan '.$value->expense_plan_id,
                                'code' => $code,
                                'old' => $value->value_text,
                                'new' => $rowInfo['final'],
                            ];
                        });
                }
            }
        }

        return $changes;
    }

    private function guardedItemNameCandidates(string $code, int $sortOrder, array $candidates): array
    {
        $guarded = array_merge($candidates, ExpenseStructureNames::knownDefaultRowTypos($code, $sortOrder));

        return array_values(array_unique(array_filter($guarded, function ($value) use ($code): bool {
            $value = trim((string) $value);

            return $value !== '' && ! ExpenseStructureNames::isPlaceholder($value, $code);
        })));
    }

    private function renderChanges(array $changes, bool $dryRun): void
    {
        $this->line($dryRun ? 'DRY RUN: no database writes will be made.' : 'Applying expense name sync.');

        foreach ([
            'sections' => 'Sections',
            'subsections' => 'Subsections',
            'default_rows' => 'Default rows',
            'plan_details' => 'Plan details',
            'plan_value_item_names' => 'Plan value item_name fields',
        ] as $key => $label) {
            $this->newLine();
            $this->info($label.': '.count($changes[$key]));

            foreach ($changes[$key] as $change) {
                $old = $change['old'] ?? '';
                $this->line(sprintf(
                    '  #%s [%s %s] %s: "%s" => "%s"',
                    $change['id'],
                    $change['code'],
                    $change['context'],
                    $key,
                    $old === null ? 'NULL' : $old,
                    $change['new']
                ));
            }
        }
    }
}
