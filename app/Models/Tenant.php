<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $primaryKey = 'tenant_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'tenant_id',
        'name',
        'slug',
        'address',
        'postcode',
        'phone',
        'mobile',
        'fax',
        'email',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'tenant_id');
    }
}
