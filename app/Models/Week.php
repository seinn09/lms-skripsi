<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable;

class Week extends Model
{
    use HasFactory, Tenantable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'course_id',
        'week_number',
        'title',
        'description',
        'tenant_id',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'week_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'week_id');
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class, 'week_id');
    }
}
