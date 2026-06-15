<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicIncomeSettingSet extends Model
{
    protected $fillable = [
        'fiscal_year',
        'name',
        'gov_doc_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function academicIncomeItems(): HasMany
    {
        return $this->hasMany(AcademicIncomeItem::class, 'setting_set_id');
    }

    public function creditUnitPrices(): HasMany
    {
        return $this->hasMany(CreditUnitPriceSetting::class, 'setting_set_id');
    }

    public function incomeRates(): HasMany
    {
        return $this->hasMany(IncomeRateSetting::class, 'setting_set_id');
    }

    public function nuolPcts(): HasMany
    {
        return $this->hasMany(NuolPctSetting::class, 'setting_set_id');
    }

    public static function latestForFiscalYear(int $fiscalYear): ?self
    {
        $withSettings = fn ($query) => $query
            ->whereHas('creditUnitPrices')
            ->orWhereHas('incomeRates')
            ->orWhereHas('nuolPcts');

        return static::where('is_active', true)
            ->where('fiscal_year', '<=', $fiscalYear)
            ->where($withSettings)
            ->orderByDesc('fiscal_year')
            ->first()
            ?? static::where('is_active', true)->where($withSettings)->orderByDesc('fiscal_year')->first()
            ?? static::where($withSettings)->orderByDesc('fiscal_year')->first()
            ?? static::where('is_active', true)->orderByDesc('fiscal_year')->first()
            ?? static::orderByDesc('fiscal_year')->first();
    }
}
