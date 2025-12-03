<?php

namespace App\Models;

use App\Models\Siswa;
use App\Models\Course;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Pengajar;
use App\Models\StaffProdi;
use App\Models\CourseClass;
use Laravel\Sanctum\HasApiTokens;
use App\Models\AssignmentSubmission;
use Laratrust\Contracts\LaratrustUser;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'label',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    public function coursesAsPengajar(): HasMany
    {
        return $this->hasMany(Course::class, 'user_id');
    }

    public function taughtClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class, 'user_id');
    }

    public function pengajar()
    {
        return $this->hasOne(Pengajar::class, 'user_id');
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'user_id');
    }

    public function staffProdi(): HasOne
    {
        return $this->hasOne(StaffProdi::class, 'user_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class, 'user_id');
    }

    public function enrolledClasses(): BelongsToMany
    {
        return $this->belongsToMany(CourseClass::class, 'course_student', 'user_id', 'course_class_id')
                    ->using(CourseStudent::class)
                    ->withPivot('final_score', 'final_grade', 'grade_point')
                    ->withTimestamps();
    }
}
