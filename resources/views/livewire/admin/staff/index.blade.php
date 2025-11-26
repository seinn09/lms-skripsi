<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $staffs;
    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->loadStaffs();
    }

    public function loadStaffs(): void
    {
        $this->staffs = User::whereHas('roles', function ($q) {
            $q->where('name', 'staff_prodi');
        })
        ->with(['staffProdi.studyProgram'])
        ->orderBy('created_at', 'desc')
        ->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus Staff ini?',
                text: 'Akun dan data profil staff akan dihapus permanen!',
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
            session()->flash('notify', ['type' => 'success', 'message' => 'Staff berhasil dihapus.']);
            $this->loadStaffs();
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Staff Prodi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Admin Program Studi</h1>

                        @permission('staff_prodis-create')
                            <a href="{{ route('admin.staff.create') }}" wire:navigate 
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Staff Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Nama Staff</th>
                                    <th>Email</th>
                                    <th>NIP</th>
                                    <th>Prodi yang di Ampu</th> 
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($staffs as $staff)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $staff->name }}</td>
                                        <td>{{ $staff->email }}</td>
                                        <td>
                                            {{ $staff->staffProdi->nip ?? '-' }}
                                        </td>
                                        <td>
                                            @if($staff->staffProdi && $staff->staffProdi->studyProgram)
                                                <span class="badge badge-outline">
                                                    {{ $staff->staffProdi->studyProgram->name }}
                                                </span>
                                            @else
                                                <span class="text-red-500 text-xs">Data Tidak Lengkap</span>
                                            @endif
                                        </td>
                                        {{-- <td class="flex gap-2">
                                            @permission('staff_prodis-delete')
                                                <button class="btn btn-xs btn-error"
                                                        wire:click="confirmDelete({{ $staff->id }})">
                                                    Delete
                                                </button>
                                            @endpermission
                                        </td> --}}
                                        <td class="flex gap-2">
                                            @permission('staff_prodis-update')
                                                <a href="{{ route('admin.staff.edit', $staff) }}" wire:navigate 
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endpermission
                                            @permission('staff_prodis-delete')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDelete({{ $staff->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center font-bold py-8 text-gray-500">
                                            Belum ada data Staff Prodi.
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