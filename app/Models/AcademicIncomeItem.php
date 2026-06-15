<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicIncomeItem extends Model
{
    protected $fillable = [
        'plan_id', 'setting_set_id', 'section_code', 'degree_program_id', 'student_count',
        'total_income', 'first_payment_amount', 'second_payment_amount',
    ];

    protected $casts = [
        'total_income'               => 'decimal:2',
        'first_payment_amount'       => 'decimal:2',
        'second_payment_amount'      => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomePlan::class, 'plan_id');
    }

    public function settingSet(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomeSettingSet::class, 'setting_set_id');
    }

    public function degreeProgram(): BelongsTo
    {
        return $this->belongsTo(DegreeProgram::class);
    }

    public function getSnapCourseCreditUnitAttribute($value = null): ?int
    {
        if (! in_array($this->section_code, ['1.1', '1.3'], true) || ! $this->degree_program_id) {
            return null;
        }

        $program = $this->degreeProgram;
        if (! $program) {
            return null;
        }

        $credit = $program->latestCourseCredit;
        if (! $credit) {
            return null;
        }

        if ($this->section_code === '1.3' && in_array($program->level, ['master', 'phd'], true)) {
            return (int) ($credit->year1_credit_unit ?? 0);
        }

        return (int) ($credit->course_credit_unit ?? 0);
    }

    public function getSnapCreditUnitPriceAttribute($value = null): ?float
    {
        if (in_array($this->section_code, ['1.1', '1.3'], true)) {
            $level = $this->degreeProgram?->level;
            if (! $level) {
                return null;
            }

            return (float) ($this->creditUnitPriceFor($level)?->credit_unit_price ?? 0);
        }

        $rateKey = match ($this->section_code) {
            '2.1' => 'item3_rate',
            '2.4' => 'item6_rate',
            default => null,
        };

        return $rateKey ? (float) ($this->incomeRateFor($rateKey)?->rate ?? 0) : null;
    }

    public function getSnapRegistrationFeeRateAttribute($value = null): ?float
    {
        $sectionType = match ($this->section_code) {
            '1.2' => 'year2_4',
            '1.4' => 'year1',
            default => null,
        };

        if ($sectionType) {
            return (float) (RegistrationFeeSetting::where('section_type', $sectionType)
                ->with('items')
                ->orderByDesc('start_year')
                ->first()?->total_rate ?? 0);
        }

        $rateKey = match ($this->section_code) {
            '2.2' => 'item4_rate',
            '2.3' => 'item5_rate',
            default => null,
        };

        return $rateKey ? (float) ($this->incomeRateFor($rateKey)?->rate ?? 0) : null;
    }

    public function getSnapNuolPctAttribute($value = null): float
    {
        if (in_array($this->section_code, ['1.1', '1.3'], true)) {
            $level = $this->degreeProgram?->level;
            if (! $level) {
                return 0.0;
            }

            return (float) ($this->nuolFor($level)?->percentage ?? match ($level) {
                'bachelor' => 0.17,
                'master', 'phd' => 0.10,
                default => 0.0,
            });
        }

        $sectionType = match ($this->section_code) {
            '1.2' => 'year2_4',
            '1.4' => 'year1',
            default => null,
        };

        if (! $sectionType) {
            return 0.0;
        }

        $setting = RegistrationFeeSetting::where('section_type', $sectionType)
            ->with('items')
            ->orderByDesc('start_year')
            ->first();

        if (! $setting || $setting->total_rate <= 0) {
            return 0.0;
        }

        return (float) ($setting->items->sum(fn ($item) => $item->amount * $item->nuol_pct) / $setting->total_rate);
    }

    private function effectiveSettingSet(): ?AcademicIncomeSettingSet
    {
        if ($this->relationLoaded('settingSet') && $this->getRelation('settingSet')) {
            return $this->getRelation('settingSet');
        }

        if ($this->setting_set_id) {
            return $this->settingSet()->first();
        }

        $fiscalYear = $this->relationLoaded('plan')
            ? $this->plan?->fiscal_year
            : $this->plan()->value('fiscal_year');

        return $fiscalYear ? AcademicIncomeSettingSet::latestForFiscalYear((int) $fiscalYear) : null;
    }

    private function creditUnitPriceFor(string $level): ?CreditUnitPriceSetting
    {
        $settingSet = $this->effectiveSettingSet();
        $query = CreditUnitPriceSetting::where('level', $level);

        if ($settingSet) {
            $scoped = CreditUnitPriceSetting::where('setting_set_id', $settingSet->id)->where('level', $level);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return $query->orderByDesc('start_year')->first();
    }

    private function incomeRateFor(string $key): ?IncomeRateSetting
    {
        $settingSet = $this->effectiveSettingSet();
        $query = IncomeRateSetting::where('key', $key);

        if ($settingSet) {
            $scoped = IncomeRateSetting::where('setting_set_id', $settingSet->id)->where('key', $key);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return $query->first();
    }

    private function nuolFor(string $level): ?NuolPctSetting
    {
        $settingSet = $this->effectiveSettingSet();
        $query = NuolPctSetting::where('level', $level);

        if ($settingSet) {
            $scoped = NuolPctSetting::where('setting_set_id', $settingSet->id)->where('level', $level);
            if ($scoped->exists()) {
                $query = $scoped;
            }
        }

        return $query->orderByDesc('start_year')->first();
    }
}
