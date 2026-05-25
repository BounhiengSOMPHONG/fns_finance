<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NuolPctSetting extends Model
{
    protected $fillable = ['level', 'percentage', 'gov_doc_id', 'start_year'];

    protected $casts = ['percentage' => 'decimal:4'];

    public static function levelLabel(string $level): string
    {
        return match ($level) {
            'bachelor'   => 'ປ.ຕີ (ປະລິນຍາຕີ)',
            'master_phd' => 'ປ.ໂທ / ປ.ເອກ (ປະລິນຍາໂທ ແລະ ເອກ)',
            default      => $level,
        };
    }

    public static function latestFor(string $level): ?self
    {
        return static::where('level', $level)->orderByDesc('start_year')->first();
    }
}
