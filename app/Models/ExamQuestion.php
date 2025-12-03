<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\Tenantable;

class ExamQuestion extends Pivot
{
    use Tenantable;

    protected $table = 'exam_questions';

    public $incrementing = true;

    protected $fillable = [
        'tenant_id',
        'exam_id',
        'question_id',
        'order',
    ];
}
