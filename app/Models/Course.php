<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_code',
        'study_program_id',
        'name',
        'description',
        'user_id',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function weeks(): HasMany
    {
        return $this->hasMany(Week::class, 'course_id');
    }

    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'course_id');
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
    }

    public function scopeSearch($query, $term)
    {
        if (empty($term)) {
            return $query;
        }

        $term = strtolower($term);
        $term = "%$term%";
        $query->where(function ($query) use ($term) {
            $query->whereRaw('LOWER(course_code) like ?', [$term])
                ->orWhereRaw('LOWER(name) like ?', [$term])
                ->orWhereHas('owner', function ($query) use ($term) {
                    $query->whereRaw('LOWER(name) like ?', [$term]);
                });
        });
    }

}