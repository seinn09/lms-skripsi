<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\StaffProdi;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public Collection $studyPrograms;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email|unique:users,email')]
    public string $email = '';

    #[Rule('required|string|min:8|confirmed')]
    public string $password = '';

    #[Rule('required|min:8')]
    public string $password_confirmation = '';

    #[Rule('nullable|string|unique:staff_prodis,nip')]
    public string $nip = '';

    #[Rule('required|exists:study_programs,id')]
    public ?int $study_program_id = null;

    public function mount(): void
    {
        $this->studyPrograms = StudyProgram::orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'label' => 'staff_prodi',
            ]);

            $role = Role::where('name', 'staff_prodi')->first();
            if ($role) {
                $user->addRole($role);
            }

            StaffProdi::create([
                'user_id' => $user->id,
                'study_program_id' => $this->study_program_id,
                'nip' => $this->nip,
            ]);

        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Staff Prodi baru berhasil ditambahkan!']);

        $this->redirectRoute('admin.staff.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Staff Prodi Baru') }}
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
                                   placeholder="Nama Staff" 
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
                            <legend class="fieldset-legend text-lg font-semibold text-blue-800 bg-blue-50 px-2">Data Penugasan</legend>

                            <label class="label" for="nip">NIP (Nomor Induk Pegawai)</label>
                            <input id="nip" type="text" class="input w-full border-black rounded-xl m-1" 
                                   placeholder="Masukkan NIP" 
                                   wire:model="nip" />
                            @error('nip') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="study_program_id">Bertugas di Program Studi</label>
                            <select id="study_program_id" class="select w-full border-black rounded-xl m-1" wire:model="study_program_id">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($studyPrograms as $prodi)
                                    <option value="{{ $prodi->id }}">
                                        {{ $prodi->name }} ({{ $prodi->degree }})
                                    </option>
                                @endforeach
                            </select>
                            <label class="label">
                                <span class="label-text-alt text-gray-500">Staff ini hanya akan memiliki akses admin untuk prodi yang dipilih.</span>
                            </label>
                            @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </fieldset>
                        
                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Staff Baru
                            </button>
                            <a href="{{ route('admin.staff.index') }}" wire:navigate 
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