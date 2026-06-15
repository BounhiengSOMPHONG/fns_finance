<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditUnitPriceSetting extends Model
{
    protected $fillable = ['setting_set_id', 'level', 'credit_unit_price', 'gov_doc_id', 'start_year'];

    protected $casts = ['credit_unit_price' => 'decimal:2'];

    public function settingSet(): BelongsTo
    {
        return $this->belongsTo(AcademicIncomeSettingSet::class, 'setting_set_id');
    }

    public static function levelLabel(string $level): string
    {
        return match ($level) {
            'bachelor' => 'ປ.ຕີ (ປະລິນຍາຕີ)',
            'master'   => 'ປ.ໂທ (ປະລິນຍາໂທ)',
            'phd'      => 'ປ.ເອກ (ປະລິນຍາເອກ)',
            default    => $level,
        };
    }
}
