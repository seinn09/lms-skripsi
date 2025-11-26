<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Pengajar;
use App\Models\Siswa;
use App\Models\Department;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    public User $user;
    public string $type = '';

    // Data Master
    public Collection $departments;
    public Collection $studyPrograms;
    public bool $isStaffProdi = false;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string|min:8|confirmed')]
    public string $password = '';
    public string $password_confirmation = '';
    public string $identity_number = '';
    public ?int $department_id = null;
    public ?int $study_program_id = null;

    public function mount(User $user): void
    {
        $this->user = $user;
        
        if ($user->hasRole('pengajar')) {
            $this->type = 'dosen';
            $this->identity_number = $user->pengajar->nip ?? '';
            $this->department_id = $user->pengajar->department_id ?? null;
        } elseif ($user->hasRole('siswa')) {
            $this->type = 'mahasiswa';
            $this->identity_number = $user->siswa->nim ?? '';
            $this->study_program_id = $user->siswa->study_program_id ?? null;
        } else {
            abort(403, 'User ini bukan Dosen atau Mahasiswa');
        }

        $this->name = $user->name;
        $this->email = $user->email;

        $this->loadMasterData();
    }

    public function loadMasterData(): void
    {
        $currentUser = auth()->user();
        $this->isStaffProdi = $currentUser->hasRole('staff_prodi');

        if ($this->isStaffProdi) {
            $staffProdi = $currentUser->staffProdi; 
            if ($staffProdi) {
                $myProdi = $staffProdi->studyProgram;
                
                $this->studyPrograms = new Collection([$myProdi]);
                $this->departments = new Collection([$myProdi->department]);
            }
        } else {
            $this->departments = Department::orderBy('name')->get();
            $this->studyPrograms = StudyProgram::with('department')->orderBy('name')->get();
        }
    }

    public function save(): void
    {
        $this->validateCommon();

        DB::transaction(function () {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
            ];
            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }
            $this->user->update($userData);

            if ($this->type === 'dosen') {
                $this->user->pengajar()->updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'department_id' => $this->department_id,
                        'nip' => $this->identity_number
                    ]
                );
            } else {
                $this->user->siswa()->updateOrCreate(
                    ['user_id' => $this->user->id],
                    [
                        'study_program_id' => $this->study_program_id,
                        'nim' => $this->identity_number
                    ]
                );
            }
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Data berhasil diperbarui!']);
        $this->redirectRoute('admin.academic.users.index', ['tab' => $this->type], navigate: true);
    }

    protected function validateCommon(): void
    {
        $this->validate([
            'name' => 'required|string|min:3',
            'password' => 'nullable|string|min:8|confirmed',
            'email' => [
                'required', 'email',
                ValidationRule::unique('users')->ignore($this->user->id),
            ],
        ]);

        if ($this->type === 'dosen') {
            $this->validate([
                'department_id' => 'required|exists:departments,id',
                'identity_number' => [
                    'required',
                    ValidationRule::unique('pengajars', 'nip')->ignore($this->user->pengajar?->id),
                ],
            ]);
        } else {
            $this->validate([
                'study_program_id' => 'required|exists:study_programs,id',
                'identity_number' => [
                    'required',
                    ValidationRule::unique('siswas', 'nim')->ignore($this->user->siswa?->id),
                ],
            ]);
        }
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit ') . ucfirst($type) . ': ' . $user->name }}
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
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1" wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="email">Email</label>
                            <input id="email" type="email" class="input w-full border-black rounded-xl m-1" wire:model="email" />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="label" for="password">Password Baru (Opsional)</label>
                                    <input id="password" type="password" class="input w-full border-black rounded-xl m-1" 
                                           placeholder="Biarkan kosong jika tidak diubah"
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
                                {{ $type === 'dosen' ? 'NIP' : 'NIM' }}
                            </label>
                            <input id="identity_number" type="text" class="input w-full border-black rounded-xl m-1" 
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
                                @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif

                        </fieldset>
                        
                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.academic.users.index', ['tab' => $type]) }}" wire:navigate 
                               class="btn btn-ghost">
                                Batal
                            </a>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>