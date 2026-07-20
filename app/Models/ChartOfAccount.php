<?php

namespace App\Models;

use Database\Factories\ChartOfAccountFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    /** @use HasFactory<ChartOfAccountFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $table = 'chart_of_accounts';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'account_code',
        'account_name',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeExpenseSelectable(Builder $query): Builder
    {
        return $query
            ->where('account_code', 'not like', '60%')
            ->where('account_code', 'not like', '61%');
    }

    /**
     * Account names from the root ancestor down to this node (e.g.
     * ["ເງິນເດືອນ...", "ເງິນເດືອນພືນຖານ", "ພະນັກງານ ສົມບູນ"]).
     * Lazy-loads parents; for bulk use, resolve in-memory in the caller.
     */
    public function lineage(): array
    {
        $names = [];
        $node = $this;
        $guard = 0;
        while ($node && $guard++ < 10) {
            array_unshift($names, $node->account_name);
            $node = $node->parent;
        }

        return $names;
    }

    public function mainCat(): string
    {
        return $this->lineage()[0] ?? '';
    }

    public function mainItem(): string
    {
        return $this->lineage()[1] ?? '';
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
        ];
    }
}
