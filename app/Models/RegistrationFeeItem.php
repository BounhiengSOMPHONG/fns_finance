<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistrationFeeItem extends Model
{
    protected $fillable = ['fee_setting_id', 'sort_order', 'name', 'amount', 'nuol_pct'];

    protected $casts = [
        'amount'   => 'decimal:2',
        'nuol_pct' => 'decimal:4',
    ];

    public function feeSetting(): BelongsTo
    {
        return $this->belongsTo(RegistrationFeeSetting::class, 'fee_setting_id');
    }
}
