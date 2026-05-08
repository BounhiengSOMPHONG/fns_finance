<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcademicIncomeItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'plan_id', 'section_code', 'sort_order', 'item_name',
        'num_credits', 'rate_per_person', 'num_persons',
        'nuol_percentage', 'student_year',
    ];

    protected $casts = [
        'nuol_percentage' => 'float',
        'rate_per_person' => 'float',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomePlan::class, 'plan_id');
    }

    public function effectiveRate(float $pricePerCredit): float
    {
        if ($this->num_credits !== null) {
            return $this->num_credits * $pricePerCredit;
        }
        return (float) ($this->rate_per_person ?? 0);
    }

    public function totalIncome(float $pricePerCredit): float
    {
        return $this->effectiveRate($pricePerCredit) * $this->num_persons;
    }

    public function nuolObligation(float $pricePerCredit): float
    {
        return $this->totalIncome($pricePerCredit) * $this->nuol_percentage;
    }

    public function kawtIncome(float $pricePerCredit): float
    {
        return $this->totalIncome($pricePerCredit) * (1 - $this->nuol_percentage);
    }
}
