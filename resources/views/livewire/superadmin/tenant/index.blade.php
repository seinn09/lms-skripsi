<?php

use Livewire\Volt\Component;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $tenants;

    public function mount(): void
    {
        $this->loadTenants();
    }

    public function loadTenants(): void
    {
        $this->tenants = Tenant::withCount('users')
                            ->orderBy('created_at', 'desc')
                            ->get();
    }

    public function deleteTenant($id): void
    {
        Tenant::where('tenant_id', $id)->delete();
        $this->loadTenants();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Tenant (Kampus)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-xl font-bold">Daftar Kampus Terdaftar</h1>
                    <a href="{{ route('superadmin.tenants.create') }}" wire:navigate class="btn btn-primary text-white btn-sm">
                        + Tambah Kampus
                    </a>
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="table">
                        <thead class="bg-base-200">
                            <tr>
                                <th>ID (Kode)</th>
                                <th>Nama Kampus</th>
                                <th>Email Kontak</th>
                                <th>Total User</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tenants as $tenant)
                                <tr class="hover">
                                    <td class="font-mono font-bold">{{ $tenant->tenant_id }}</td>
                                    <td>
                                        <div class="font-bold">{{ $tenant->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $tenant->address }}</div>
                                    </td>
                                    <td>{{ $tenant->email ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-ghost">
                                            {{ $tenant->users_count }}
                                        </span>
                                    </td>
                                    <td class="flex gap-2">
                                        <a href="{{ route('superadmin.tenants.edit', $tenant) }}" wire:navigate class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">Edit</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-8">Belum ada tenant.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
