<?php

use Livewire\Volt\Component;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Livewire\WithTenantFilter;

new class extends Component
{
    use WithTenantFilter;

    public Collection $studyPrograms;
    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->loadTenantFilter();
        $this->loadStudyPrograms();
    }

    public function updatedSelectedTenant(): void
    {
        $this->loadStudyPrograms();
    }

    public function loadStudyPrograms(): void
    {
        $query = StudyProgram::with(['department.faculty'])
            ->withCount('students')
            ->orderBy('name');

        $this->applyTenantFilter($query);

        $this->studyPrograms = $query->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus Program Studi ini?',
                text: 'Data program studi dan relasi terkait akan dihapus permanen!',
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
            StudyProgram::destroy($this->idToDelete);
            $this->idToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Program Studi berhasil dihapus.']);
            $this->loadStudyPrograms();
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Program Studi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <x-filter-tenant :tenants="$tenants_filter_list" wire:model.live="selectedTenant" />

                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Program Studi</h1>

                        @permission('study_programs-create')
                            <a href="{{ route('admin.study-programs.create') }}" wire:navigate
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Program Studi Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Nama Program Studi</th>
                                    <th>Jenjang</th>
                                    <th>Departemen</th>
                                    <th>Fakultas</th>
                                    <th>Jumlah Mahasiswa</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($studyPrograms as $prodi)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $prodi->name }}</td>
                                        <td>
                                            <span class="badge badge-primary">{{ $prodi->degree }}</span>
                                        </td>
                                        <td>
                                            @if($prodi->department)
                                                {{ $prodi->department->name }}
                                            @else
                                                <span class="text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($prodi->department && $prodi->department->faculty)
                                                <span class="badge badge-outline">
                                                    {{ $prodi->department->faculty->name }}
                                                </span>
                                            @else
                                                <span class="text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-outline">
                                                {{ $prodi->students_count }} Mahasiswa
                                            </span>
                                        </td>
                                        <td class="flex gap-2">
                                            @permission('study_programs-update')
                                                <a href="{{ route('admin.study-programs.edit', $prodi) }}" wire:navigate
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endpermission
                                            @permission('study_programs-delete')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDelete({{ $prodi->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center font-bold py-8 text-gray-500">
                                            Belum ada data Program Studi.
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
