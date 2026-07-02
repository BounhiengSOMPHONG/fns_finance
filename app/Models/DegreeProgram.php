<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DegreeProgram extends Model
{
    public const DEPARTMENTS = [
        'math_stats' => ['label' => 'ພາກວິຊາຄະນິດສາດ ແລະ ສະຖິຕິ', 'order' => 10],
        'physics' => ['label' => 'ພາກວິຊາຟິຊິກສາດ', 'order' => 20],
        'chemistry' => ['label' => 'ພາກວິຊາເຄມີສາດ', 'order' => 30],
        'biology' => ['label' => 'ພາກວິຊາຊີວະວິທະຍາ', 'order' => 40],
        'computer_science' => ['label' => 'ພາກວິຊາວິທະຍາສາດຄອມພິວເຕີ', 'order' => 50],
        'other' => ['label' => 'ອື່ນໆ', 'order' => 90],
    ];

    protected $fillable = [
        'code',
        'name',
        'level',
        'study_year',
        'is_active',
        'academic_department',
        'department_sort_order',
        'include_in_planning',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'include_in_planning' => 'boolean',
    ];

    public function scopeIncludedInPlanning(Builder $query): Builder
    {
        return $query->where('include_in_planning', true);
    }

    public function scopePlanningOrder(Builder $query): Builder
    {
        return $query
            ->orderBy('department_sort_order')
            ->orderBy('level')
            ->orderByRaw('study_year IS NULL')
            ->orderBy('study_year')
            ->orderBy('name')
            ->orderBy('id');
    }

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

    public function getAcademicDepartmentLabelAttribute(): string
    {
        return self::DEPARTMENTS[$this->academic_department]['label'] ?? self::DEPARTMENTS['other']['label'];
    }

    public static function departmentOptions(): array
    {
        return collect(self::DEPARTMENTS)
            ->map(fn (array $meta, string $key): array => [
                'key' => $key,
                'label' => $meta['label'],
                'order' => $meta['order'],
            ])
            ->values()
            ->all();
    }

    public static function departmentOrder(?string $department): int
    {
        return self::DEPARTMENTS[$department]['order'] ?? self::DEPARTMENTS['other']['order'];
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
