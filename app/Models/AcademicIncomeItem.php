<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicIncomeItem extends Model
{
    protected $fillable = [
        'plan_id', 'setting_set_id', 'section_code', 'degree_program_id', 'student_count',
        'snap_credit_unit_price', 'snap_course_credit_unit',
        'snap_registration_fee_rate', 'snap_nuol_pct',
        'total_income', 'first_payment_amount', 'second_payment_amount',
    ];

    protected $casts = [
        'snap_credit_unit_price'     => 'decimal:2',
        'snap_registration_fee_rate' => 'decimal:2',
        'snap_nuol_pct'              => 'decimal:4',
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
}
