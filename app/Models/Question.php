<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'type', // multiple_choice, essay, true_false
        'question_text',
        'weight',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(Option::class, 'question_id');
    }

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_questions', 'question_id', 'exam_id');
    }

    public function scopeSearch($query, $term)
    {
        $term = "%$term%";
        $query->where('question_text', 'ilike', $term);
    }

    public function scopeType($query, $type)
    {
        if ($type && in_array($type, ['multiple_choice', 'essay'])) {
            $query->where('type', $type);
        }
    }
}