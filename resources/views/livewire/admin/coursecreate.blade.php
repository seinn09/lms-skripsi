<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Course;
use App\Models\Tenant;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudyProgram;
use App\Models\Week;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule as ValidationRule;

use function Livewire\Volt\state;
use function Livewire\Volt\on;

new class extends Component
{
    public Collection $tenants;
    public Collection $faculties;
    public Collection $departments;
    public Collection $studyPrograms;
    public Collection $pengajars;

    public bool $isSuperAdmin = false;
    public ?string $tenant_id = null;
    public ?int $faculty_id = null;
    public ?int $department_id = null;

    #[Rule('required|string|min:5')]
    public string $course_code = '';

    #[Rule('required|min:3')]
    public string $name = '';

    #[Rule('required|min:10')]
    public string $description = '';

    #[Rule('required|exists:study_programs,id')]
    public ?int $study_program_id = null;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null;

    public function mount(): void
    {
        $user = Auth::user();
        $this->isSuperAdmin = $user->hasRole('superadministrator');

        if ($this->isSuperAdmin) {
            $this->tenants = Tenant::orderBy('name')->get();
        } else {
            $this->tenants = new Collection();
            $this->tenant_id = $user->tenant_id;
        }

        $this->faculties = new Collection();
        $this->departments = new Collection();
        $this->studyPrograms = new Collection();
        $this->pengajars = new Collection();
    }

    public function updatedTenantId($value)
    {
        if (empty($value)) {
            $this->tenant_id = null;
            $this->faculties = new Collection();
        } else {
            $this->faculties = Faculty::where('tenant_id', $value)
                ->orderBy('name')
                ->get();
        }

        $this->departments = new Collection();
        $this->studyPrograms = new Collection();
        $this->pengajars = new Collection();
        $this->faculty_id = null;
        $this->department_id = null;
        $this->study_program_id = null;
        $this->user_id = null;
    }

    public function updatedFacultyId($value)
    {
        if (empty($value)) {
            $this->faculty_id = null;
            $this->departments = new Collection();
        } else {
            $currentTenantId = $this->isSuperAdmin ? $this->tenant_id : Auth::user()->tenant_id;

            $this->departments = Department::where('faculty_id', $value)
                ->where('tenant_id', $currentTenantId)
                ->orderBy('name')
                ->get();
        }

        $this->studyPrograms = new Collection();
        $this->pengajars = new Collection();
        $this->department_id = null;
        $this->study_program_id = null;
        $this->user_id = null;
    }

    public function updatedDepartmentId($value)
    {
        if (empty($value)) {
            $this->department_id = null;
            $this->studyPrograms = new Collection();
        } else {
            $currentTenantId = $this->isSuperAdmin ? $this->tenant_id : Auth::user()->tenant_id;

            $this->studyPrograms = StudyProgram::where('department_id', $value)
                ->where('tenant_id', $currentTenantId)
                ->orderBy('name')
                ->get();
        }

        $this->pengajars = new Collection();
        $this->study_program_id = null;
        $this->user_id = null;
    }

    public function updatedStudyProgramId($value)
    {
        $this->loadPengajars();
    }

    public function loadPengajars(): void
    {
        if (empty($this->study_program_id)) {
            $this->pengajars = new Collection();
            return;
        }

        $currentTenantId = $this->isSuperAdmin ? $this->tenant_id : Auth::user()->tenant_id;

        $this->pengajars = User::whereHas('roles', function ($query) {
            $query->where('name', 'pengajar');
        })
        ->whereHas('pengajar', function ($query) {
            $query->where('study_program_id', $this->study_program_id);
        })
        ->where('tenant_id', $currentTenantId)
        ->orderBy('name', 'asc')
        ->get();

        if ($this->pengajars->isNotEmpty() && empty($this->user_id)) {
            $this->user_id = $this->pengajars->first()->id;
        }
    }

    public function save(): void
    {
        $currentTenantId = $this->isSuperAdmin ? $this->tenant_id : Auth::user()->tenant_id;

        $this->validate([
            'tenant_id' => $this->isSuperAdmin ? 'required' : 'nullable',
            'course_code' => [
                'required',
                'string',
                'min:5',
                ValidationRule::unique('courses', 'course_code')->where(function ($query) use ($currentTenantId) {
                    if ($currentTenantId) {
                        return $query->where('tenant_id', $currentTenantId);
                    }
                    return $query->whereNull('tenant_id');
                }),
            ],
            'name' => 'required|min:3',
            'description' => 'required|min:10',
            'study_program_id' => 'required|exists:study_programs,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // Additional validation: verify lecturer belongs to selected study program
        $lecturer = User::whereHas('pengajar', function ($query) {
            $query->where('study_program_id', $this->study_program_id);
        })->find($this->user_id);

        if (!$lecturer) {
            $this->addError('user_id', 'Dosen yang dipilih tidak terdaftar di Program Studi yang dipilih.');
            return;
        }

        DB::transaction(function () use ($currentTenantId) {

            $course = Course::create([
                'tenant_id' => $currentTenantId,
                'course_code' => $this->course_code,
                'name' => $this->name,
                'description' => $this->description,
                'study_program_id' => $this->study_program_id,
                'user_id' => $this->user_id,
            ]);

            for ($i = 1; $i <= 16; $i++) {
                Week::create([
                    'tenant_id' => $currentTenantId,
                    'course_id' => $course->id,
                    'week_number' => $i,
                    'title' => "Pertemuan Ke-$i",
                    'description' => "Materi untuk pertemuan minggu ke-$i akan diisi oleh dosen."
                ]);
            }
        });

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Mata kuliah baru (dan 16 pertemuannya) berhasil dibuat!'
        ]);

        $this->redirectRoute('courses.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Mata Kuliah Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">

                        <fieldset class="fieldset bg-base-200 border-base-300 rounded-btn-primarybox w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Mata Kuliah</legend>

                            <label class="label" for="course_code">Kode Mata Kuliah</label>
                            <input id="course_code" type="text" class="input w-full border-black rounded-xl"
                                   placeholder="Cth: NINFUM6039"
                                   wire:model="course_code" />
                            @error('course_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label" for="name">Nama Mata Kuliah</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl"
                                   placeholder="Contoh: Pemrograman Web Lanjut"
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label" for="description">Deskripsi</label>
                            <textarea id="description" class="textarea w-full border-black rounded-xl"
                                      placeholder="Deskripsi singkat mata kuliah..."
                                      wire:model="description"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @if($isSuperAdmin)
                            <label class="label mt-4" for="tenant_id">Kampus (Tenant)</label>
                            <select id="tenant_id" class="select w-full border-black rounded-xl" wire:model.live="tenant_id">
                                <option value="">-- Pilih Kampus --</option>
                                @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->tenant_id }}">{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                            @error('tenant_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                            <label class="label mt-4" for="faculty_id">Fakultas</label>
                            <select id="faculty_id" class="select w-full border-black rounded-xl" wire:model.live="faculty_id" @if($faculties->isEmpty()) disabled @endif>
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach ($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                @endforeach
                            </select>

                            <label class="label mt-4" for="department_id">Departemen</label>
                            <select id="department_id" class="select w-full border-black rounded-xl" wire:model.live="department_id" @if($departments->isEmpty()) disabled @endif>
                                <option value="">-- Pilih Departemen --</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>

                            <label class="label mt-4" for="study_program_id">Program Studi</label>
                            <select id="study_program_id" class="select w-full border-black rounded-xl" wire:model.live="study_program_id" @if($studyPrograms->isEmpty()) disabled @endif>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($studyPrograms as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                @endforeach
                            </select>
                            @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="user_id">Dosen Pengampu (PJ Mata Kuliah)</label>
                            <select id="user_id" class="select w-full border-black rounded-xl" wire:model="user_id" @if($pengajars->isEmpty()) disabled @endif>
                                @if($pengajars->isEmpty())
                                    <option value="">-- Pilih Program Studi terlebih dahulu --</option>
                                @else
                                    @foreach ($pengajars as $pengajar)
                                        <option value="{{ $pengajar->id }}">{{ $pengajar->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <div class="mt-6 flex gap-3">
                                <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                 text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                    Simpan
                                </button>
                                <a href="{{ route('courses.index') }}" wire:navigate
                                   class="btn btn-ghost bg-gray-200 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300">
                                    Batal
                                </a>
                            </div>

                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
