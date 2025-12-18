<?php

namespace App\Traits\Livewire;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

trait WithTenantInput
{
    public ?string $tenant_id = null;
    public Collection $tenants_list;
    public bool $isSuperAdminForTenant = false;

    public function loadTenantData(): void
    {
        $user = Auth::user();
        $this->isSuperAdminForTenant = $user->hasRole('superadministrator');

        if ($this->isSuperAdminForTenant) {
            $this->tenants_list = Tenant::orderBy('name')->get();
        } else {
            $this->tenants_list = new Collection();
            $this->tenant_id = $user->tenant_id;
        }
    }

    public function getTenantValidationRule(): string
    {
        return $this->isSuperAdminForTenant
            ? 'required|exists:tenants,tenant_id'
            : 'nullable';
    }

    public function getValidTenantId(): ?string
    {
        if ($this->isSuperAdminForTenant) {
            return $this->tenant_id;
        }

        return Auth::user()->tenant_id;
    }

    public function isSuperAdmin(): bool
    {
        return $this->isSuperAdminForTenant;
    }
}
