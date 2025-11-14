<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $courses;

    public ?int $idToDelete = null;

    public function mount(): void
    {
        $user = Auth::user();

        if ($user->hasRole('superadministrator|admin')) {
            $this->courses = Course::with('owner')
                                ->orderBy('name', 'asc')
                                ->get();
        } 
        elseif ($user->hasRole('pengajar')) {
            $this->courses = $user->coursesAsPengajar()
                                ->with('owner')
                                ->orderBy('name', 'asc')
                                ->get();
        }
        else {
            $this->courses = collect();
        }
    }

    public function confirmDeleteCourse(int $id): void
    {
        $this->idToDelete = $id;

        $this->js("
            Swal.fire({
                title: 'Anda akan menghapus data ini!',
                text: 'Menghapus mata kuliah akan menghapus SEMUA kelas dan materi di dalamnya!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
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
        if ($this->idToDelete === null) {
            return;
        }

        Course::destroy($this->idToDelete);

        $this->idToDelete = null;

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Mata kuliah berhasil dihapus!'
        ]);

        $this.mount();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Mata Kuliah (Katalog)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Mata Kuliah</h1>

                        @role('superadministrator|admin')
                            <a href="{{ route('admin.courses.create') }}" wire:navigate 
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Course Baru
                            </a>
                        @endrole
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Kode MK</th> 
                                    <th>Nama Mata Kuliah</th>
                                    <th>Dosen Pengampu</th> 
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr class="hover:bg-base-300">
                                        <td>
                                            <span class="badge badge-info">{{ $course->course_code }}</span>
                                        </td>
                                        <td>{{ $course->name }}</td>
                                        <td>
                                            {{ $course->owner->name ?? 'N/A' }}
                                        </td>
                                        <td>
                                            {{ $course->pengajar->name ?? 'N/A' }}
                                        </td>
                                        <td class="flex gap-2">
                                            <div class="card-actions justify-end">
                                                <a href="{{ route('courses.materials.index', $course) }}" wire:navigate 
                                                class="py-2 px-4 text-base rounded-md text-black bg-blue-400
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-500">
                                                    Lihat Materi
                                                </a>
                                            </div>

                                            <a href="{{ route('courses.detail', $course) }}" wire:navigate
                                               class="py-2 px-4 text-base rounded-md text-black bg-green-400
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-green-500">>
                                                Detail
                                            </a>
                                            
                                            @role('superadministrator|admin')
                                                <a href="{{ route('admin.courses.edit', $course) }}" wire:navigate 
                                                    class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                    Edit
                                                </a>
                                            @endrole
                                            @role('superadministrator|admin')
                                                <button class="py-2 px-4 text-base rounded-md bg-red-600 text-black
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700"
                                                wire:click="confirmDeleteCourse({{ $course->id }})">Delete</button>
                                            @endrole
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold">Belum ada data mata kuliah.</td>
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