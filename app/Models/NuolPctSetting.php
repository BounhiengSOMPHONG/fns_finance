<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NuolPctSetting extends Model
{
    protected $fillable = ['level', 'percentage', 'gov_doc_id', 'start_year'];

    protected $casts = ['percentage' => 'decimal:4'];

    public function academicIncomeItems(): HasMany
    {
        return $this->hasMany(AcademicIncomeItem::class);
    }

    public static function levelLabel(string $level): string
    {
        return match ($level) {
            'bachelor' => 'ປ.ຕີ (ປະລິນຍາຕີ)',
            'master' => 'ປ.ໂທ (ປະລິນຍາໂທ)',
            'phd' => 'ປ.ເອກ (ປະລິນຍາເອກ)',
            'master_phd' => 'ປ.ໂທ / ປ.ເອກ', // legacy fallback
            default => $level,
        };
    }

    public static function latestFor(string $level): ?self
    {
        return static::where('level', $level)->orderByDesc('start_year')->first();
    }
}
