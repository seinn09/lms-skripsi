<?php

use Livewire\Volt\Component;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;

new class extends Component
{
    public Collection $users;

    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->users = User::with('roles')->orderBy('created_at', 'asc')->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;

        $this->js("
            Swal.fire({
                title: 'Anda akan menghapus data ini!',
                text: 'Data yang dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Panggil method 'destroy' di backend
                    \$wire.destroy();
                }
            })
        ");
    }

    public function destroy(): void
    {
        if ($this->idToDelete === null) {
            return;
        }

        User::destroy($this->idToDelete);

        $this->idToDelete = null;

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Pengguna berhasil dihapus!'
        ]);

        $this->users = User::with('roles')->get();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Pengguna</h1>

                        @permission('users-create')
                            <a href="{{ route('admin.pengguna.create') }}" wire:navigate class="btn btn-primary btn-sm text-white">
                                + Tambah Pengguna Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            {{ $user->roles->first() ? $user->roles->first()->display_name : 'N/A' }}
                                        </td>
                                        <td class="flex gap-2">
                                            @permission('users-update')
                                                <a href="{{ route('admin.pengguna.edit', $user) }}" wire:navigate 
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endpermission
                                            @permission('users-delete')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDelete({{ $user->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold">Belum ada data pengguna.</td>
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