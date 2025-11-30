<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourseClass extends Model
{
    use HasFactory;

    protected $table = 'course_classes';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'class_code',
        'semester',
        'capacity',
        'status',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function pengajar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_student', 'course_class_id', 'user_id');
    }

    public function scopeSearch($query, $term)
    {
        $term = strtolower($term);
        $term = "%$term%";
        $query->where(function ($query) use ($term) {
            $query->whereRaw('LOWER(class_code) like ?', [$term])
                ->orWhereHas('course', function ($query) use ($term) {
                    $query->whereRaw('LOWER(name) like ?', [$term]);
                })
                ->orWhereHas('pengajar', function ($query) use ($term) {
                    $query->whereRaw('LOWER(name) like ?', [$term]);
                });
        });
    }
}