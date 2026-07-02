<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegistrationFeeSetting extends Model
{
    protected $fillable = ['section_type', 'gov_doc_id', 'start_year'];

    public function items(): HasMany
    {
        return $this->hasMany(RegistrationFeeItem::class, 'fee_setting_id')->orderBy('sort_order');
    }

    public function academicIncomeItems(): HasMany
    {
        return $this->hasMany(AcademicIncomeItem::class);
    }

    public function getTotalRateAttribute(): float
    {
        return $this->items->sum('amount');
    }

    public static function sectionLabel(string $type): string
    {
        return match ($type) {
            'year2_4' => 'ນ/ສ ປີ 2–4',
            'year1' => 'ນ/ສ ປີ 1 (ໃໝ່)',
            default => $type,
        };
    }
}
