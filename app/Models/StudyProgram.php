<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Traits\Tenantable;

class StudyProgram extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = ['department_id', 'name', 'code', 'degree'];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'study_program_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Siswa::class, 'study_program_id');
    }

    public function staff(): HasOne
    {
        // Model StaffProdi akan kita buat sebentar lagi
        return $this->hasOne(StaffProdi::class, 'study_program_id');
    }
}
