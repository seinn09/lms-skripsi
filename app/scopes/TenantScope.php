<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class TenantScope implements Scope
{
    /**
     * Terapkan scope ke query builder.
     */
    public function apply(Builder $builder, Model $model)
    {
        if (Auth::check() && Auth::user()->hasRole('superadministrator')) {
            return;
        }

        $tenantId = session('tenant_id');

        if ($tenantId) {
            $builder->where($model->getTable() . '.tenant_id', $tenantId);
        }
    }
}
