<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use App\Models\User;
use App\Models\Role;
use App\Models\Pengajar;
use App\Models\Siswa;
use App\Models\Faculty;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;
use App\Traits\Livewire\WithTenantInput;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    use WithTenantInput;

    #[Url]
    public string $type = 'dosen';

    public array $faculties = [];
    public array $departments = [];
    public array $studyPrograms = [];
    public bool $isStaffProdi = false;

    public ?int $faculty_id = null;
    public ?int $department_id = null;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public string $password = '';
    public string $password_confirmation = '';

    public string $identity_number = '';

    public ?int $study_program_id = null;

    public function mount(): void
    {
        $this->loadTenantData();

        if (!in_array($this->type, ['dosen', 'mahasiswa', 'staff_prodi'])) {
            $this->type = 'dosen';
        }

        $this->loadMasterData();
    }

    public function updatedTenantId(): void
    {
        $this->loadFaculties();
        $this->faculty_id = null;
        $this->department_id = null;
        $this->study_program_id = null;
        $this->departments = [];
        $this->studyPrograms = [];
    }

    public function updatedFacultyId(): void
    {
        $this->loadDepartments();
        $this->department_id = null;
        $this->study_program_id = null;
        $this->studyPrograms = [];
    }

    public function updatedDepartmentId(): void
    {
        $this->loadStudyPrograms();
        $this->study_program_id = null;
    }

    public function loadMasterData(): void
    {
        $user = auth()->user();
        $this->isStaffProdi = $user->hasRole('staff_prodi');

        if ($this->isStaffProdi) {
            $staffProdi = $user->staffProdi;

            if ($staffProdi) {
                $myProdi = $staffProdi->studyProgram;
                $this->studyPrograms = [$myProdi];
                $this->study_program_id = $myProdi->id;
            }
        } else {
            $this->loadFaculties();
        }
    }

    protected function loadFaculties(): void
    {
        if ($this->isStaffProdi) {
            return;
        }

        if ($this->isSuperAdmin()) {
            if (!empty($this->tenant_id)) {
                $this->faculties = Faculty::where('tenant_id', $this->tenant_id)
                    ->orderBy('name')
                    ->get()
                    ->all();
            } else {
                $this->faculties = [];
            }
        } else {
            $this->faculties = Faculty::orderBy('name')->get()->all();
        }
    }

    protected function loadDepartments(): void
    {
        if ($this->isStaffProdi || empty($this->faculty_id)) {
            $this->departments = [];
            return;
        }

        $currentTenantId = $this->getValidTenantId();

        $this->departments = Department::where('faculty_id', $this->faculty_id)
            ->where('tenant_id', $currentTenantId)
            ->orderBy('name')
            ->get()
            ->all();
    }

    protected function loadStudyPrograms(): void
    {
        if ($this->isStaffProdi || empty($this->department_id)) {
            $this->studyPrograms = [];
            return;
        }

        $currentTenantId = $this->getValidTenantId();

        $this->studyPrograms = StudyProgram::where('department_id', $this->department_id)
            ->where('tenant_id', $currentTenantId)
            ->orderBy('name')
            ->get()
            ->all();
    }

    public function save(): void
    {
        $currentTenantId = $this->getValidTenantId();

        $this->validate([
            'tenant_id' => $this->getTenantValidationRule(),
            'email' => [
                'required',
                'email',
                ValidationRule::unique('users', 'email')->where(function ($query) use ($currentTenantId) {
                    if ($currentTenantId) {
                        return $query->where('tenant_id', $currentTenantId);
                    }
                    return $query->whereNull('tenant_id');
                }),
            ],
            'identity_number' => [ // Asumsi nama field inputnya identity_number
                'required',
                ValidationRule::unique($this->type === 'dosen' ? 'pengajars' : 'siswas', $this->type === 'dosen' ? 'nip' : 'nim')
                    ->where(function ($query) use ($currentTenantId) {
                        if ($currentTenantId) {
                            return $query->where('tenant_id', $currentTenantId);
                        }
                        return $query->whereNull('tenant_id');
                    }),
            ],

            'study_program_id' => [
                'required',
                ValidationRule::exists('study_programs', 'id')->where(function ($query) use ($currentTenantId) {
                    return $query->where('tenant_id', $currentTenantId);
                }),
            ],
        ]);

        if ($this->type === 'dosen') {
            $this->createDosen();
        } else {
            $this->createMahasiswa();
        }

        session()->flash('notify', ['type' => 'success', 'message' => ucfirst($this->type) . ' baru berhasil ditambahkan!']);

        $this->redirectRoute('admin.academic.users.index', ['tab' => $this->type], navigate: true);
    }

    protected function createDosen(): void
    {
        DB::transaction(function () {
            $user = $this->createUserBase('pengajar');

            $role = Role::where('name', 'pengajar')->first();
            $user->addRole($role);

            Pengajar::create([
                'user_id' => $user->id,
                'study_program_id' => $this->study_program_id,
                'nip' => $this->identity_number,
            ]);
        });
    }

    protected function createMahasiswa(): void
    {
        DB::transaction(function () {
            $user = $this->createUserBase('siswa');

            $role = Role::where('name', 'siswa')->first();
            $user->addRole($role);

            Siswa::create([
                'user_id' => $user->id,
                'study_program_id' => $this->study_program_id,
                'nim' => $this->identity_number,
            ]);
        });
    }

    protected function createUserBase(string $label): User
    {
        return User::create([
            'tenant_id' => $this->getValidTenantId(),
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'label' => $label,
        ]);
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ $type === 'dosen' ? 'Tambah Dosen Baru' : ($type === 'mahasiswa' ? 'Tambah Mahasiswa Baru' : 'Tambah Staff Prodi Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">

                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Data Akun Login</legend>

                            <x-input-tenant :tenants="$tenants_list" />

                            <label class="label" for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1"
                                   placeholder="Nama Lengkap"
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="email">Email</label>
                            <input id="email" type="email" class="input w-full border-black rounded-xl m-1"
                                   placeholder="email@kampus.ac.id"
                                   wire:model="email" />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="label" for="password">Password</label>
                                    <input id="password" type="password" class="input w-full border-black rounded-xl m-1"
                                           wire:model="password" />
                                    @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="label" for="password_confirmation">Konfirmasi Password</label>
                                    <input id="password_confirmation" type="password" class="input w-full border-black rounded-xl m-1"
                                           wire:model="password_confirmation" />
                                </div>
                            </div>
                        </fieldset>

                        <fieldset class="fieldset bg-blue-50 border-blue-200 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold text-blue-800 bg-blue-50 px-2">
                                Data Akademik {{ ucfirst($type) }}
                            </legend>

                            <label class="label" for="identity_number">
                                {{ $type === 'dosen' ? 'NIP (Nomor Induk Pegawai)' : ($type === 'mahasiswa' ? 'NIM (Nomor Induk Mahasiswa)' : 'NIP (Nomor Induk Pegawai)') }}
                            </label>
                            <input id="identity_number" type="text" class="input w-full border-black rounded-xl m-1"
                                   placeholder="{{ $type === 'dosen' || $type === 'staff_prodi' ? 'Masukkan NIP' : 'Masukkan NIM' }}"
                                   wire:model="identity_number" />
                            @error('identity_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @if($type === 'dosen')
                                @if(!$isStaffProdi)
                                    <label class="label mt-4" for="faculty_id">Fakultas</label>
                                    <select id="faculty_id" class="select w-full border-black rounded-xl m-1" wire:model.live="faculty_id">
                                        <option value="">-- Pilih Fakultas --</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                        @endforeach
                                    </select>

                                    <label class="label mt-4" for="department_id">Departemen</label>
                                    <select id="department_id" class="select w-full border-black rounded-xl m-1" wire:model.live="department_id">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                @endif

                                <label class="label mt-4" for="study_program_id">Program Studi</label>
                                <select id="study_program_id"
                                        class="select w-full border-black rounded-xl m-1 disabled:bg-gray-200 disabled:text-gray-500"
                                        wire:model="study_program_id"
                                        @if($isStaffProdi) disabled @endif>

                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach ($studyPrograms as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                    @endforeach
                                </select>
                                @if($isStaffProdi) <span class="text-xs text-gray-500 mt-1">Otomatis terisi sesuai penugasan Anda.</span> @endif
                                @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                            @if($type === 'mahasiswa')
                                @if(!$isStaffProdi)
                                    <label class="label mt-4" for="faculty_id">Fakultas</label>
                                    <select id="faculty_id" class="select w-full border-black rounded-xl m-1" wire:model.live="faculty_id">
                                        <option value="">-- Pilih Fakultas --</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                        @endforeach
                                    </select>

                                    <label class="label mt-4" for="department_id">Departemen</label>
                                    <select id="department_id" class="select w-full border-black rounded-xl m-1" wire:model.live="department_id">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                @endif

                                <label class="label mt-4" for="study_program_id">Program Studi</label>
                                <select id="study_program_id"
                                        class="select w-full border-black rounded-xl m-1 disabled:bg-gray-200 disabled:text-gray-500"
                                        wire:model="study_program_id"
                                        @if($isStaffProdi) disabled @endif>

                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach ($studyPrograms as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                    @endforeach
                                </select>
                                @if($isStaffProdi) <span class="text-xs text-gray-500 mt-1">Otomatis terisi sesuai penugasan Anda.</span> @endif
                                @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                            @if($type === 'staff_prodi')
                                @if(!$isStaffProdi)
                                    <label class="label mt-4" for="faculty_id">Fakultas</label>
                                    <select id="faculty_id" class="select w-full border-black rounded-xl m-1" wire:model.live="faculty_id">
                                        <option value="">-- Pilih Fakultas --</option>
                                        @foreach($faculties as $faculty)
                                            <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                        @endforeach
                                    </select>

                                    <label class="label mt-4" for="department_id">Departemen</label>
                                    <select id="department_id" class="select w-full border-black rounded-xl m-1" wire:model.live="department_id">
                                        <option value="">-- Pilih Departemen --</option>
                                        @foreach($departments as $dept)
                                            <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                        @endforeach
                                    </select>
                                @endif

                                <label class="label mt-4" for="study_program_id">Program Studi</label>
                                <select id="study_program_id"
                                        class="select w-full border-black rounded-xl m-1 disabled:bg-gray-200 disabled:text-gray-500"
                                        wire:model="study_program_id"
                                        @if($isStaffProdi) disabled @endif>

                                    <option value="">-- Pilih Program Studi --</option>
                                    @foreach ($studyPrograms as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                    @endforeach
                                </select>
                                @if($isStaffProdi) <span class="text-xs text-gray-500 mt-1">Otomatis terisi sesuai penugasan Anda.</span> @endif
                                @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                        </fieldset>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan {{ ucfirst($type) }}
                            </button>
                            <a href="{{ route('admin.academic.users.index', ['tab' => $type]) }}" wire:navigate
                               class="btn btn-ghost transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110">
                                Batal
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
