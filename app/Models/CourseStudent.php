<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Traits\Tenantable;

class CourseStudent extends Pivot
{
    use Tenantable;

    protected $table = 'course_student';

    public $incrementing = true;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'course_class_id',
        'final_score',
        'final_grade',
        'grade_point',
    ];
}
