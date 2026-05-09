<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicIncomeDefault extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'section_code',
        'sort_order',
        'item_name',
        'num_credits',
        'nuol_percentage',
        'student_year',
    ];
}
