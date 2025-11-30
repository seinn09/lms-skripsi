<?php

use Livewire\Volt\Component;
use App\Models\CourseClass;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Collection $classes;
    public string $search = '';

    public $selectedFaculty = null;
    public $selectedDept = null;
    public $selectedProdi = null;

    public Collection $faculties;
    public Collection $departments;
    public Collection $studyPrograms;

    public function mount(): void
    {
        $this->faculties = Faculty::orderBy('name')->get();
        $this->departments = new Collection();
        $this->studyPrograms = new Collection();
        
        $this->loadClasses();
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
        $this->loadClasses();
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
        $this->loadClasses();
    }

    public function updatedSelectedProdi($value)
    {
        if (empty($value)) {
            $this->selectedProdi = null;
        }
        $this->loadClasses();
    }

    public function updatedSearch(): void
    {
        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        $query = CourseClass::with(['course.studyProgram.department.faculty', 'pengajar'])
                            ->search($this->search);

        $user = Auth::user();
        if ($user->hasRole('staff_prodi') && $user->staffProdi) {
             $query->whereHas('course', function ($q) use ($user) {
                $q->where('study_program_id', $user->staffProdi->study_program_id);
             });
        }

        if (!empty($this->selectedFaculty)) {
            $query->whereHas('course.studyProgram.department', function ($q) {
                $q->where('faculty_id', $this->selectedFaculty);
            });
        }

        if (!empty($this->selectedDept)) {
            $query->whereHas('course.studyProgram', function ($q) {
                $q->where('department_id', $this->selectedDept);
            });
        }

        if (!empty($this->selectedProdi)) {
            $query->whereHas('course', function ($q) {
                $q->where('study_program_id', $this->selectedProdi);
            });
        }

        $this->classes = $query->orderBy('created_at', 'asc')->get();
    }

    public function openEnrollment(int $classId): void
    {
        $class = CourseClass::find($classId);
        if ($class) {
            $class->update(['status' => 'open']);
        }

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Pendaftaran kelas berhasil dibuka!'
        ]);

        $this->loadClasses();
    }

    public function closeEnrollment(int $classId): void
    {
        $class = CourseClass::find($classId);
        if ($class) {
            $class->update(['status' => 'closed']);
        }

        session()->flash('notify', [
            'type' => 'warning',
            'message' => 'Pendaftaran kelas telah ditutup.'
        ]);

        $this->loadClasses();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pendaftaran Mata Kuliah') }}
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
                        <h1 class="text-xl font-bold">Daftar Semua Mata Kuliah</h1>
                        <div class="w-1/3">
                            <input type="text" wire:model.live="search" class="input w-full border-black rounded-xl" placeholder="Cari Mata Kuliah, Kode Kelas, atau Dosen...">
                        </div>
                    </div>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Mata Kuliah</th>
                                    <th>Kode Kelas</th>
                                    <th>Dosen Pengampu</th>
                                    <th>Semester</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $class)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $class->course->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-ghost">{{ $class->class_code }}</span></td>
                                        <td>{{ $class->pengajar->name ?? 'N/A' }}</td>
                                        <td>{{ $class->semester }}</td>
                                        <td>{{ $class->capacity }}</td>
                                        <td>
                                            @if ($class->status == 'open')
                                                <span class="badge badge-success">Dibuka</span>
                                            @else
                                                <span class="badge badge-error">Ditutup</span>
                                            @endif
                                        </td>
                                        
                                        <td class="flex gap-2">
                                            @if ($class->status == 'open')
                                                 <button type="submit" wire:click="closeEnrollment({{ $class->id }})" class="py-2 px-4 bg-red-400 font-bold rounded-md
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-500">
                                                    Tutup Pendaftaran
                                                </button>
                                            @else
                                                <button type="submit" wire:click="openEnrollment({{ $class->id }})" class="py-2 px-4 bg-blue-500 font-bold rounded-md
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                                    Buka Pendaftaran
                                                </button>
                                            @endif
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