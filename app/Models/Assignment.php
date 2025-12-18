<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable;

class Assignment extends Model
{
    use HasFactory, Tenantable;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'week_id',
        'title',
        'description',
        'deadline',
        'tenant_id',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class, 'week_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(AssignmentSubmission::class, 'assignment_id');
    }
}
