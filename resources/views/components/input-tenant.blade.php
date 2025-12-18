@props(['tenants'])

@if($this->isSuperAdminForTenant)
    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <label class="label font-bold text-yellow-800" for="tenant_id">
            Pilih Kampus (Tenant)
        </label>

        <select id="tenant_id"
                class="select w-full border-yellow-400 rounded-xl"
                wire:model.live="tenant_id">
            <option value="">-- Pilih Kampus --</option>
            @foreach ($tenants as $tenant)
                <option value="{{ $tenant->tenant_id }}">{{ $tenant->name }}</option>
            @endforeach
        </select>

        <label class="label">
            <span class="label-text-alt text-gray-500">
                Wajib diisi karena Anda login sebagai Superadmin.
            </span>
        </label>

        @error('tenant_id')
            <span class="text-red-500 text-sm block mt-1">{{ $message }}</span>
        @enderror
    </div>
@endif
