<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicIncomeItem extends Model
{
    protected $fillable = [
        'plan_id', 'section_code', 'degree_program_id', 'student_count',
        'snap_credit_unit_price', 'snap_course_credit_unit', 'snap_registration_fee_rate',
        'snap_nuol_pct', 'total_income',
    ];

    protected $casts = [
        'snap_credit_unit_price' => 'decimal:2',
        'snap_course_credit_unit' => 'decimal:2',
        'snap_registration_fee_rate' => 'decimal:2',
        'snap_nuol_pct' => 'decimal:4',
        'total_income' => 'decimal:2',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomePlan::class, 'plan_id');
    }

    public function degreeProgram(): BelongsTo
    {
        return $this->belongsTo(DegreeProgram::class);
    }

    public function getFirstPaymentAmountAttribute(): float
    {
        return round((float) $this->total_income - $this->getTeachingFeeAmountAttribute(), 2);
    }

    public function getSecondPaymentAmountAttribute(): float
    {
        return $this->getTeachingFeeAmountAttribute();
    }

    public function getTeachingFeeAmountAttribute(): float
    {
        return round((float) $this->total_income * $this->teachingFeePercentage(), 2);
    }

    private function teachingFeePercentage(): float
    {
        if (! in_array($this->section_code, ['1.1', '1.3'], true)) {
            return 0.0;
        }

        return match ($this->degreeProgram?->level) {
            'master', 'phd' => 0.60,
            'bachelor' => 0.40,
            default => 0.0,
        };
    }

    public function getSnapCourseCreditUnitAttribute($value = null): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

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
            return (float) ($credit->course_credit_unit ?? 0) * CourseCreditSplitSetting::year1For($program->level);
        }

        if ($this->section_code === '1.1' && in_array($program->level, ['master', 'phd'], true)) {
            return (float) ($credit->course_credit_unit ?? 0) * CourseCreditSplitSetting::year2For($program->level);
        }

        return (float) ($credit->course_credit_unit ?? 0);
    }

    public function getSnapCreditUnitPriceAttribute($value = null): ?float
    {
        if ($value !== null) {
            return (float) $value;
        }

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
        if ($value !== null) {
            return (float) $value;
        }

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
        if ($value !== null) {
            return (float) $value;
        }

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

    private function creditUnitPriceFor(string $level): ?CreditUnitPriceSetting
    {
        return CreditUnitPriceSetting::where('level', $level)->orderByDesc('start_year')->first();
    }

    private function incomeRateFor(string $key): ?IncomeRateSetting
    {
        return IncomeRateSetting::where('key', $key)->first();
    }

    private function nuolFor(string $level): ?NuolPctSetting
    {
        return NuolPctSetting::where('level', $level)->orderByDesc('start_year')->first();
    }
}
