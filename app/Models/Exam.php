<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable;

class Exam extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = [
        'week_id',
        'title',
        'description',
        'duration_minutes',
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class, 'week_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'exam_questions', 'exam_id', 'question_id')
                    ->using(ExamQuestion::class)
                    ->withPivot('order')
                    ->orderByPivot('order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'exam_id');
    }
}
