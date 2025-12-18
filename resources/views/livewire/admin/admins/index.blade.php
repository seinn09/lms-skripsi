<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Livewire\WithTenantFilter;

new class extends Component
{
    use WithTenantFilter;

    public Collection $admins;
    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->loadTenantFilter();
        $this->loadAdmins();
    }

    public function updatedSelectedTenant(): void
    {
        $this->loadAdmins();
    }

    public function loadAdmins(): void
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        })
        ->with(['tenant'])
        ->orderBy('created_at', 'desc');

        $this->applyTenantFilter($query);

        $this->admins = $query->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus Admin ini?',
                text: 'Akun admin akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.destroy();
                }
            })
        ");
    }

    public function destroy(): void
    {
        if ($this->idToDelete) {
            User::destroy($this->idToDelete);
            $this->idToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Admin berhasil dihapus.']);
            $this->loadAdmins();
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Admin') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <x-filter-tenant :tenants="$tenants_filter_list" wire:model.live="selectedTenant" />

                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Admin</h1>

                        @permission('admins-create')
                            <a href="{{ route('admin.admins.create') }}" wire:navigate
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Admin Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Nama Admin</th>
                                    <th>Email</th>
                                    <th>Kampus</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($admins as $admin)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $admin->name }}</td>
                                        <td>{{ $admin->email }}</td>
                                        <td>
                                            @if($admin->tenant)
                                                <span class="badge badge-outline">
                                                    {{ $admin->tenant->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="flex gap-2">
                                            @permission('admins-update')
                                                <a href="{{ route('admin.admins.edit', $admin) }}" wire:navigate
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endpermission
                                            @permission('admins-delete')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDelete({{ $admin->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold py-8 text-gray-500">
                                            Belum ada data Admin.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
