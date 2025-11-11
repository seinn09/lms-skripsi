<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Week;
use App\Models\Material;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Course $course;
    public Week $week;
    public Collection $materials;

    public ?Week $previousWeek = null;
    public ?Week $nextWeek = null;

    public ?int $idToDelete = null;

    public function mount(Course $course, Week $week): void
    {
        $this->course = $course;
        $this->week = $week;

        $this->loadMaterials();

        $this->previousWeek = Week::where('course_id', $this->course->id)
                                ->where('week_number', $this->week->week_number - 1)
                                ->first();
        
        $this->nextWeek = Week::where('course_id', $this->course->id)
                                ->where('week_number', $this->week->week_number + 1)
                                ->first();
    }

    public function loadMaterials(): void
    {
        $this->materials = $this->week->materials()
                                ->orderBy('title', 'asc')
                                ->get();
    }

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
        Material::destroy($this->idToDelete);
        $this->idToDelete = null;
        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Materi berhasil dihapus!'
        ]);
        $this->loadMaterials();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">{{ $week->title }}</h1>
                    
                    @if ($week->description)
                        <div class="prose max-w-none">
                            <p>{{ $week->description }}</p>
                        </div>
                    @else
                        <p class="text-gray-500">Belum ada deskripsi untuk pertemuan ini.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">File & Link Materi</h1>

                        @permission('materials-create')
                            <a href="{{ route('materials.create', ['week' => $week]) }}" wire:navigate class="btn btn-primary btn-sm text-white">
                                + Tambah Materi
                            </a>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Judul Materi</th>
                                    <th>Tipe</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($materials as $material)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $material->title }}</td>
                                        <td>
                                            @if ($material->file_path)
                                                <span class="badge badge-info badge-m">File</span>
                                            @elseif ($material->external_link)
                                                <span class="badge badge-success badge-m">Link Eksternal</span>
                                            @endif
                                        </td>
                                        
                                        <td class="flex gap-2">
                                            @if ($material->file_path)
                                                <a href="{{ Storage::url($material->file_path) }}" download="{{ $material->title }}.{{ pathinfo($material->file_path, PATHINFO_EXTENSION) }}"
                                                   class="btn btn-xs btn-success">
                                                    Download
                                                </a>
                                            @elseif ($material->external_link)
                                                <a href="{{ $material->external_link }}" target="_blank" class="btn btn-xs btn-info">Buka Link</a>
                                            @endif
                                            
                                            @permission('materials-update')
                                                <a href="{{ route('materials.edit', $material) }}" wire:navigate class="btn btn-xs btn-warning">
                                                    Edit
                                                </a>
                                            @endpermission

                                            @permission('materials-delete')
                                                <button class="btn btn-xs py-2 px-4 rounded-md bg-red-600 text-black"
                                                wire:click="confirmDeleteMaterial({{ $material->id }})">Delete</button>
                                            @endpermission
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center font-bold">Belum ada file atau link untuk pertemuan ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="flex justify-between mt-6">
                        @if ($previousWeek)
                            <a href="{{ route('courses.materials.show', ['course' => $course, 'week' => $previousWeek]) }}" wire:navigate
                               class="btn btn-ghost">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                                {{ \Illuminate\Support\Str::limit($previousWeek->title, 20) }}
                            </a>
                        @else
                            <div></div>
                        @endif

                        @if ($nextWeek)
                            <a href="{{ route('courses.materials.show', ['course' => $course, 'week' => $nextWeek]) }}" wire:navigate
                               class="btn btn-ghost">
                                {{ \Illuminate\Support\Str::limit($nextWeek->title, 20) }}
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>