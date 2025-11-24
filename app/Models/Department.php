<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['faculty_id', 'name', 'code'];

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(Faculty::class, 'faculty_id');
    }

    public function studyPrograms(): HasMany
    {
        return $this->hasMany(StudyProgram::class, 'department_id');
    }

    public function pengajars(): HasMany
    {
        return $this->hasMany(Pengajar::class, 'department_id');
    }
}