<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Url;
use App\Models\User;
use App\Models\Role;
use App\Models\Pengajar;
use App\Models\Siswa;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    #[Url]
    public string $type = 'dosen';

    public Collection $departments;
    public Collection $studyPrograms;
    public bool $isStaffProdi = false;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public string $password = '';
    public string $password_confirmation = '';

    public string $identity_number = '';

    public ?int $department_id = null;
    public ?int $study_program_id = null;


    public function mount(): void
    {
        if (!in_array($this->type, ['dosen', 'mahasiswa'])) {
            $this->type = 'dosen';
        }

        $this->loadMasterData();
    }

    public function loadMasterData(): void
    {
        $user = auth()->user();
        $this->isStaffProdi = $user->hasRole('staff_prodi');

        if ($this->isStaffProdi) {
            $staffProdi = $user->staffProdi; 

            if ($staffProdi) {
                $myProdi = $staffProdi->studyProgram;

                $this->studyPrograms = new Collection([$myProdi]);
                $this->departments = new Collection([$myProdi->department]);

                $this->study_program_id = $myProdi->id;
                $this->department_id = $myProdi->department_id;
            }
        } else {
            $this->departments = Department::orderBy('name')->get();
            $this->studyPrograms = StudyProgram::with('department')->orderBy('name')->get();
        }
    }

    public function save(): void
    {
        $this->validateCommon();

        if ($this->type === 'dosen') {
            $this->createDosen();
        } else {
            $this->createMahasiswa();
        }

        session()->flash('notify', ['type' => 'success', 'message' => ucfirst($this->type) . ' baru berhasil ditambahkan!']);
        
        $this->redirectRoute('admin.academic.users.index', ['tab' => $this->type], navigate: true);
    }

    protected function validateCommon(): void
    {
        $this->validate();

        if ($this->type === 'dosen') {
            $this->validate([
                'identity_number' => 'required|unique:pengajars,nip',
                'department_id' => 'required|exists:departments,id',
            ], [
                'identity_number.required' => 'NIP wajib diisi.',
                'identity_number.unique' => 'NIP sudah terdaftar.',
            ]);
        } else {
            $this->validate([
                'identity_number' => 'required|unique:siswas,nim',
                'study_program_id' => 'required|exists:study_programs,id',
            ], [
                'identity_number.required' => 'NIM wajib diisi.',
                'identity_number.unique' => 'NIM sudah terdaftar.',
            ]);
        }
    }

    protected function createDosen(): void
    {
        DB::transaction(function () {
            $user = $this->createUserBase('pengajar');
            
            $role = Role::where('name', 'pengajar')->first();
            $user->addRole($role);

            Pengajar::create([
                'user_id' => $user->id,
                'department_id' => $this->department_id,
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
            {{ $type === 'dosen' ? 'Tambah Dosen Baru' : 'Tambah Mahasiswa Baru' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Data Akun Login</legend>

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
                                {{ $type === 'dosen' ? 'NIP (Nomor Induk Pegawai)' : 'NIM (Nomor Induk Mahasiswa)' }}
                            </label>
                            <input id="identity_number" type="text" class="input w-full border-black rounded-xl m-1" 
                                   placeholder="{{ $type === 'dosen' ? 'Masukkan NIP' : 'Masukkan NIM' }}" 
                                   wire:model="identity_number" />
                            @error('identity_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @if($type === 'dosen')
                                <label class="label mt-4" for="department_id">Homebase Departemen</label>
                                <select id="department_id" 
                                        class="select w-full border-black rounded-xl m-1 disabled:bg-gray-200 disabled:text-gray-500" 
                                        wire:model="department_id"
                                        @if($isStaffProdi) disabled @endif>
                                    
                                    <option value="">-- Pilih Departemen --</option>
                                    @foreach ($departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>
                                @if($isStaffProdi) <span class="text-xs text-gray-500 mt-1">Otomatis terisi sesuai penugasan Anda.</span> @endif
                                @error('department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                            @if($type === 'mahasiswa')
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