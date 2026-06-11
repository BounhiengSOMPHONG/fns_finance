<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpenseSubsectionDefaultRow extends Model
{
    protected $fillable = [
        'subsection_code',
        'item_name',
        'reference',
        'chart_of_account_id',
        'note',
        'sort_order',
        'default_values',
        'is_active',
    ];

    protected $casts = [
        'default_values' => 'array',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'chart_of_account_id');
    }
}
