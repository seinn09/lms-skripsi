<?php

use Livewire\Volt\Component;
use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Livewire\WithTenantFilter;

new class extends Component
{
    use WithTenantFilter;

    public Collection $departments;
    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->loadTenantFilter();
        $this->loadDepartments();
    }

    public function updatedSelectedTenant(): void
    {
        $this->loadDepartments();
    }

    public function loadDepartments(): void
    {
        $query = Department::with(['faculty'])
            ->withCount('studyPrograms')
            ->orderBy('name');

        $this->applyTenantFilter($query);

        $this->departments = $query->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus Departemen ini?',
                text: 'Data departemen dan relasi terkait akan dihapus permanen!',
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
            Department::destroy($this->idToDelete);
            $this->idToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Departemen berhasil dihapus.']);
            $this->loadDepartments();
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Departemen') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <x-filter-tenant :tenants="$tenants_filter_list" wire:model.live="selectedTenant" />

                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Departemen</h1>

                        @permission('departments-create')
                            <a href="{{ route('admin.departments.create') }}" wire:navigate
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Departemen Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Kode</th>
                                    <th>Nama Departemen</th>
                                    <th>Fakultas</th>
                                    <th>Jumlah Prodi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($departments as $department)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $department->code }}</td>
                                        <td>{{ $department->name }}</td>
                                        <td>
                                            @if($department->faculty)
                                                <span class="badge badge-outline">
                                                    {{ $department->faculty->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-outline">
                                                {{ $department->study_programs_count }} Prodi
                                            </span>
                                        </td>
                                        <td class="flex gap-2">
                                            @permission('departments-update')
                                                <a href="{{ route('admin.departments.edit', $department) }}" wire:navigate
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endpermission
                                            @permission('departments-delete')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDelete({{ $department->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center font-bold py-8 text-gray-500">
                                            Belum ada data Departemen.
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
