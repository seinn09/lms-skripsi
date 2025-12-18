<?php

namespace App\Traits\Livewire;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait WithTenantFilter
{
    public ?string $selectedTenant = null;
    public Collection $tenants_filter_list;

    public function loadTenantFilter(): void
    {
        if (Auth::user()->hasRole('superadministrator')) {
            $this->tenants_filter_list = Tenant::orderBy('name')->get();
        } else {
            $this->tenants_filter_list = new Collection();
        }
    }

    /**
     * Gunakan ini di query builder Anda.
     * Contoh: $query = User::query(); $this->applyTenantFilter($query);
     */
    public function applyTenantFilter(Builder $query): Builder
    {
        if (Auth::user()->hasRole('superadministrator') && !empty($this->selectedTenant)) {
            $table = $query->getModel()->getTable();
            $query->where("$table.tenant_id", $this->selectedTenant);
        }

        return $query;
    }
}
