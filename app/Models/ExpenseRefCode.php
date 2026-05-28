<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseRefCode extends Model
{
    protected $fillable = ['code', 'label', 'account_code', 'sort_order'];
}
