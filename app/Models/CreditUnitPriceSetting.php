<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditUnitPriceSetting extends Model
{
    protected $fillable = ['level', 'credit_unit_price', 'gov_doc_id', 'start_year'];

    protected $casts = ['credit_unit_price' => 'decimal:2'];

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
