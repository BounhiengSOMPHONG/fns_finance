<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseCreditSetting extends Model
{
    protected $fillable = ['degree_program_id', 'course_credit_unit', 'gov_doc_id', 'start_year'];

    public function degreeProgram(): BelongsTo
    {
        return $this->belongsTo(DegreeProgram::class);
    }
}
