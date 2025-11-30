<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Url; // Agar tab tersimpan di URL
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    #[Url]
    public string $tab = 'dosen';

    public Collection $users;
    public ?int $idToDelete = null;

    public function mount(): void
    {
        if (!in_array($this->tab, ['dosen', 'mahasiswa'])) {
            $this->tab = 'dosen';
        }
        
        $this->loadUsers();
    }

    public function updatedTab(): void
    {
        $this->loadUsers();
    }

    public function loadUsers(): void
    {
        $currentUser = Auth::user();
        $query = User::query();

        if ($this->tab === 'dosen') {
            $query->whereHas('roles', fn($q) => $q->where('name', 'pengajar'))
                  ->with('pengajar.studyProgram');

            if ($currentUser->hasRole('staff_prodi')) {
                $prodiId = $currentUser->staffProdi->study_program_id;
                $query->whereHas('pengajar', fn($q) => $q->where('study_program_id', $prodiId));
            }

        } else {
            $query->whereHas('roles', fn($q) => $q->where('name', 'siswa'))
                  ->with('siswa.studyProgram');

            if ($currentUser->hasRole('staff_prodi')) {
                $prodiId = $currentUser->staffProdi->study_program_id;
                $query->whereHas('siswa', fn($q) => $q->where('study_program_id', $prodiId));
            }
        }

        $this->users = $query->orderBy('created_at', 'desc')->get();
    }

    public function confirmDelete(int $id): void
    {
        $this->idToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus Pengguna?',
                text: 'Akun dan semua data terkait akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { \$wire.destroy(); }
            })
        ");
    }

    public function destroy(): void
    {
        if ($this->idToDelete) {
            User::destroy($this->idToDelete);
            $this->idToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Pengguna berhasil dihapus.']);
            $this->loadUsers();
        }
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Akademik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex flex-row md:flex-row justify-between items-center mb-6 gap-4">
                        
                        <div role="tablist" class="tabs tabs-boxed">
                            <a role="tab" 
                               class="tab {{ $tab === 'dosen' ? 'tab-active' : '' }}"
                               wire:click="$set('tab', 'dosen')">
                               Data Dosen
                            </a>
                            <a role="tab" 
                               class="tab {{ $tab === 'mahasiswa' ? 'tab-active' : '' }}"
                               wire:click="$set('tab', 'mahasiswa')">
                               Data Mahasiswa
                            </a>
                        </div>

                        @if($tab === 'dosen')
                            @permission('pengajars-create')
                                <a href="{{ route('admin.academic.users.create', ['type' => 'dosen']) }}" wire:navigate class="btn btn-primary btn-sm text-white">
                                    + Tambah Dosen
                                </a>
                            @endpermission
                        @else
                            @permission('siswas-create')
                                <a href="{{ route('admin.academic.users.create', ['type' => 'mahasiswa']) }}" wire:navigate class="btn btn-primary btn-sm text-white">
                                    + Tambah Mahasiswa
                                </a>
                            @endpermission
                        @endif
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Nama Lengkap</th>
                                    <th>Email</th>
                                    <th>{{ $tab === 'dosen' ? 'NIP / Program Studi' : 'NIM / Prodi' }}</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if ($tab === 'dosen')
                                                <div class="flex flex-col">
                                                    <span class="font-semibold">{{ $user->pengajar->nip ?? '-' }}</span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $user->pengajar->studyProgram->name ?? 'Prodi tidak set' }}
                                                    </span>
                                                </div>
                                            @else
                                                <div class="flex flex-col">
                                                    <span class="font-semibold">{{ $user->siswa->nim ?? '-' }}</span>
                                                    <span class="text-xs text-gray-500">
                                                        {{ $user->siswa->studyProgram->name ?? 'Prodi tidak set' }}
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="flex gap-2">
                                            @if (($tab === 'dosen' && auth()->user()->isAbleTo('pengajars-delete')) || 
                                                 ($tab === 'mahasiswa' && auth()->user()->isAbleTo('siswas-delete')))
                                                
                                                <a href="{{ route('admin.academic.users.edit', $user) }}" class="py-2 px-4 text-base rounded-md bg-yellow-400 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-500">Edit</a>
                                                
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                        wire:click="confirmDelete({{ $user->id }})">
                                                    Delete
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold py-8 text-gray-500">
                                            Belum ada data {{ $tab }}.
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