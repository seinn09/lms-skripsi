<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable;

class Material extends Model
{
    use HasFactory, Tenantable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'week_id',
        'title',
        'file_path',
        'external_link',
    ];

    public function week(): BelongsTo
    {
        return $this->belongsTo(Week::class, 'week_id');
    }
}
