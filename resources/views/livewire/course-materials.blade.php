<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    // Properti untuk menampung data
    public Course $course;
    public Collection $materials;

    // Properti untuk delete
    public ?int $idToDelete = null;

    /**
     * Mount dijalankan saat komponen dimuat.
     * Livewire akan otomatis mengambil Course dari URL.
     */
    public function mount(Course $course): void
    {
        // 1. Muat course dan materi-materinya
        $this->course = $course;
        $this->materials = $course->materials()->orderBy('created_at', 'desc')->get();
    }

    /**
     * Dipanggil oleh tombol "Delete"
     * Menyimpan ID dan memunculkan SweetAlert
     */
    public function confirmDeleteMaterial(int $id): void
    {
        $this->idToDelete = $id;

        $this->js("
            Swal.fire({
                title: 'Anda akan menghapus materi ini!',
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

    /**
     * Dipanggil oleh $wire.destroy() dari JavaScript
     * Menghapus data material
     */
    public function destroy(): void
    {
        if ($this->idToDelete === null) {
            return;
        }

        Material::destroy($this->idToDelete);
        $this->idToDelete = null;

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Materi berhasil dihapus!'
        ]);

        // Muat ulang (refresh) daftar materi
        $this->materials = $this->course->materials()->orderBy('created_at', 'desc')->get();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{-- Menampilkan nama mata kuliah di header --}}
            Materi untuk: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Materi</h1>

                        @permission('materials-create')
                            <a href="#" wire:navigate class="btn btn-primary btn-sm">
                                + Tambah Materi Baru
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Pertemuan</th>
                                    <th>Judul Materi</th>
                                    <th>Deskripsi Singkat</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($materials as $material)
                                    <tr class="hover:bg-base-300">
                                        <td></td>
                                        <td>{{ $material->title }}</td>
                                        <td>{{ \Illuminate\Support\Str::limit($material->description, 70) }}</td>
                                        
                                        <td class="flex gap-2">
                                            @permission('materials-read')
                                                <button class="btn btn-xs btn-success">Download</button>
                                            @endpermission
                                            
                                            @permission('materials-update')
                                                <a href="#" wire:navigate class="btn btn-xs btn-warning">
                                                    Edit
                                                </a>
                                            @endpermission

                                            @permission('materials-delete')
                                                <button class="btn btn-xs btn-error"
                                                        wire:click="confirmDeleteMaterial({{ $material->id }})">
                                                    Delete
                                                </button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center font-bold">Belum ada materi untuk mata kuliah ini.</td>
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