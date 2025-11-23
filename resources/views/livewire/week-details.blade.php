<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Week;
use App\Models\Material;
use App\Models\Assignment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;

new class extends Component
{
    public Course $course;
    public Week $week;
    public Collection $materials;
    public Collection $assignments;

    public ?Week $previousWeek = null;
    public ?Week $nextWeek = null;

    public ?int $materialToDelete = null;
    public ?int $assignmentToDelete = null;

    public function mount(Course $course, Week $week): void
    {
        $this->course = $course;
        $this->week = $week;
        $this->loadItems();

        $this->previousWeek = Week::where('course_id', $this->course->id)
                                ->where('week_number', $this->week->week_number - 1)
                                ->first();
        
        $this->nextWeek = Week::where('course_id', $this->course->id)
                                ->where('week_number', $this->week->week_number + 1)
                                ->first();
    }

    public function loadItems(): void
    {
        $this->materials = $this->week->materials()->orderBy('title', 'asc')->get();
        $this->assignments = $this->week->assignments()->orderBy('title', 'asc')->get();
    }

    #[Computed]
    public function items()
    {
        return $this->materials->merge($this->assignments)->sortBy('title');
    }

    public function confirmDeleteMaterial(int $id): void
    {
        $this->materialToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus materi ini?',
                text: 'File/link ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.destroyMaterial();
                }
            })
        ");
    }

    public function destroyMaterial(): void
    {
        if ($this->materialToDelete === null) return;

        $material = Material::find($this->materialToDelete);

        if ($material->file_path) {
            Storage::disk('public')->delete($material->file_path);
        }
        
        $material->delete();
        $this->materialToDelete = null;

        session()->flash('notify', ['type' => 'success', 'message' => 'Materi berhasil dihapus!']);
        $this->loadItems();
    }

    public function confirmDeleteAssignment(int $id): void
    {
        $this->assignmentToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus tugas ini?',
                text: 'Tugas dan semua submisi mahasiswa akan dihapus!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.destroyAssignment();
                }
            })
        ");
    }

    public function destroyAssignment(): void
    {
        if ($this->assignmentToDelete === null) return;

        Assignment::destroy($this->assignmentToDelete); 
        $this->assignmentToDelete = null;

        session()->flash('notify', ['type' => 'success', 'message' => 'Tugas berhasil dihapus!']);
        $this->loadItems();
    }

    public function showCreateOptions(): void
    {
        $user = auth()->user();
        $canCreateMaterial = $user->isAbleTo('materials-create');
        $canCreateAssignment = $user->isAbleTo('assignments-create');
        $materialUrl = $canCreateMaterial ? route('materials.create', ['week' => $this->week]) : null;
        $assignmentUrl = $canCreateAssignment ? route('assignments.create', ['week' => $this->week]) : null;

        $js = "
            Swal.fire({
                title: 'Tambah Apa?',
                text: 'Pilih tipe item yang ingin Anda tambahkan:',
                icon: 'question',
                showConfirmButton: " . ($canCreateMaterial ? 'true' : 'false') . ",
                confirmButtonText: 'Materi (File/Link)',
                confirmButtonColor: '#3085d6',
                showDenyButton: " . ($canCreateAssignment ? 'true' : 'false') . ",
                denyButtonText: 'Tugas (Assignment)',
                denyButtonColor: '#B22222',
                showCancelButton: true,
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Livewire.navigate('$materialUrl'); 
                } else if (result.isDenied) {
                    Livewire.navigate('$assignmentUrl');
                }
            })
        ";

        $this->js(preg_replace(array(
            '/\s{2,}/',
            '/\t/',
            '/\n/'
        ), ' ', $js));
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold">{{ $week->title }}</h1>
                        @permission('weeks-update')
                            <a href="{{ route('courses.materials.edit', ['week' => $week]) }}" wire:navigate
                               class="btn btn-ghost btn-sm btn-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </a>
                        @endpermission
                    </div>
                    
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
                        <h1 class="text-xl font-bold">Materi & Tugas Pertemuan</h1>

                        @permission('materials-create|assignments-create')
                            <button class="btn btn-sm bg-blue-600 px-4 by-1 font-bold
                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500" 
                                    wire:click="showCreateOptions">
                                + Tambah
                            </button>
                        @endpermission
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Judul</th>
                                    <th>Tipe</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->items as $item)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $item->title }}</td>
                                        
                                        <td>
                                            @if ($item instanceof \App\Models\Assignment)
                                                <span class="badge badge-error badge-sm">Tugas</span>
                                            @elseif ($item->file_path)
                                                <span class="badge badge-info badge-sm">File</span>
                                            @elseif ($item->external_link)
                                                <span class="badge badge-success badge-sm">Link</span>
                                            @endif
                                        </td>
                                        
                                        <td class="flex gap-2">
                                            @if ($item instanceof \App\Models\Assignment)
                                                @permission('submissions-create')
                                                    <a href="#" class="btn btn-xs bg-blue-500 font-bold
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">Lihat Tugas</a>
                                                @endpermission
                                                @permission('assignments-update')
                                                    <a href="{{ route('assignments.edit', $item) }}" class="btn btn-xs bg-yellow-500 font-bold
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-orange-500">Edit</a>
                                                @endpermission
                                                @permission('assignments-delete')
                                                    <button class="btn btn-xs bg-red-500 font-bold text-xs px-2
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-rose-700"
                                                            wire:click="confirmDeleteAssignment({{ $item->id }})">
                                                        Delete
                                                    </button>
                                                @endpermission
                                            @else
                                                @if ($item->file_path)
                                                    <a href="{{ Storage::url($item->file_path) }}" 
                                                       download="{{ $item->title }}.{{ pathinfo($item->file_path, PATHINFO_EXTENSION) }}"
                                                       class="btn btn-xs bg-blue-500 font-bold
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                                        Download
                                                    </a>
                                                @elseif ($item->external_link)
                                                    <a href="{{ $item->external_link }}" target="_blank" class="btn btn-xs bg-blue-500 font-bold
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-700">Buka Link</a>
                                                @endif
                                                
                                                @permission('materials-update')
                                                    <a href="{{ route('materials.edit', $item) }}" wire:navigate
                                                       class="btn btn-xs bg-yellow-500 font-bold
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">
                                                        Edit
                                                    </a>
                                                @endpermission
                                                @permission('materials-delete')
                                                    <button class="btn btn-xs bg-red-500 font-bold text-xs px-2
                                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-rose-700"
                                                            wire:click="confirmDeleteAssignment({{ $item->id }})">
                                                        Delete
                                                    </button>
                                                @endpermission
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center font-bold">Belum ada materi atau tugas untuk pertemuan ini.</td>
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