<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\Tenantable;

class Faculty extends Model
{
    use HasFactory, Tenantable;

    protected $fillable = ['name', 'code'];

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class, 'faculty_id');
    }
}
