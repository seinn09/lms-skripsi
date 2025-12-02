<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Collection $courses;
    public string $search = '';

    public $selectedFaculty = null;
    public $selectedDept = null;
    public $selectedProdi = null;

    public Collection $faculties;
    public Collection $departments;
    public Collection $studyPrograms;

    public ?int $idToDelete = null;

    public function mount(): void
    {
        $this->faculties = Faculty::orderBy('name')->get();
        $this->departments = new Collection();
        $this->studyPrograms = new Collection();
        
        $this->loadCourses();
    }

    public function updatedSearch()
    {
        $this->loadCourses();
    }

    public function updatedSelectedFaculty($value)
    {
        if (empty($value)) {
            $this->selectedFaculty = null;
            $this->departments = new Collection();
        } else {
            $this->departments = Department::where('faculty_id', $value)->orderBy('name')->get();
        }
        
        $this->studyPrograms = new Collection();
        $this->selectedDept = null;
        $this->selectedProdi = null;
        $this->loadCourses();
    }

    public function updatedSelectedDept($value)
    {
        if (empty($value)) {
            $this->selectedDept = null;
            $this->studyPrograms = new Collection();
        } else {
            $this->studyPrograms = StudyProgram::where('department_id', $value)->orderBy('name')->get();
        }

        $this->selectedProdi = null;
        $this->loadCourses();
    }

    public function updatedSelectedProdi($value)
    {
        if (empty($value)) {
            $this->selectedProdi = null;
        }
        $this->loadCourses();
    }

    public function loadCourses(): void
    {
        $user = Auth::user();
        $query = Course::with(['owner', 'studyProgram.department.faculty'])
                    ->search($this->search);

        if ($user->hasRole('staff_prodi') && $user->staffProdi) {
            $query->where('study_program_id', $user->staffProdi->study_program_id);
        }
        elseif ($user->hasRole('pengajar')) {
            $query->where('user_id', $user->id);
        }

        if (!empty($this->selectedFaculty)) {
            $query->whereHas('studyProgram.department', function ($q) {
                $q->where('faculty_id', $this->selectedFaculty);
            });
        }

        if (!empty($this->selectedDept)) {
            $query->whereHas('studyProgram', function ($q) {
                $q->where('department_id', $this->selectedDept);
            });
        }

        if (!empty($this->selectedProdi)) {
            $query->where('study_program_id', $this->selectedProdi);
        }

        $this->courses = $query->orderBy('name', 'asc')->get();
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

                    @role('superadministrator|admin')
                    <div class="flex flex-col gap-4 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Filter Fakultas --}}
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Filter Fakultas</span>
                                </label>
                                <select wire:model.live="selectedFaculty" class="select select-bordered w-full">
                                    <option value="">Semua Fakultas</option>
                                    @foreach($faculties as $faculty)
                                        <option value="{{ $faculty->id }}">{{ $faculty->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Departemen --}}
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Filter Departemen</span>
                                </label>
                                <select wire:model.live="selectedDept" class="select select-bordered w-full" @if($departments->isEmpty()) disabled @endif>
                                    <option value="">Semua Departemen</option>
                                    @foreach($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Prodi --}}
                            <div class="form-control w-full">
                                <label class="label">
                                    <span class="label-text font-semibold">Filter Program Studi</span>
                                </label>
                                <select wire:model.live="selectedProdi" class="select select-bordered w-full" @if($studyPrograms->isEmpty()) disabled @endif>
                                    <option value="">Semua Program Studi</option>
                                    @foreach($studyPrograms as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    @endrole

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Mata Kuliah</h1>
                        <div class="flex gap-3 items-center">
                            <input type="text" wire:model.live="search" class="input w-full border-black rounded-xl" placeholder="Cari Kode MK, Nama, atau Dosen...">

                            @permission('courses-create')
                                <a href="{{ route('admin.courses.create') }}" wire:navigate 
                                class="btn btn-primary btn-sm text-white">
                                    + Tambah Course Baru
                                </a>
                            @endpermission
                        </div>
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

                                            @permission('submissions-read')
                                                <a href="{{ route('courses.gradebook', $course) }}" wire:navigate
                                                   class="py-2 px-4 text-base rounded-md text-black bg-violet-400
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-violet-500">
                                                    Nilai
                                                </a>
                                            @endpermission

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