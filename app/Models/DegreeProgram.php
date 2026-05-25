<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DegreeProgram extends Model
{
    protected $fillable = ['code', 'name', 'level', 'study_year', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function courseCredits(): HasMany
    {
        return $this->hasMany(CourseCreditSetting::class);
    }

    public function latestCourseCredit(): HasOne
    {
        return $this->hasOne(CourseCreditSetting::class)->latestOfMany('start_year');
    }

    public function getLevelLabelAttribute(): string
    {
        return match ($this->level) {
            'bachelor' => 'ປ.ຕີ',
            'master'   => 'ປ.ໂທ',
            'phd'      => 'ປ.ເອກ',
            default    => $this->level,
        };
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
