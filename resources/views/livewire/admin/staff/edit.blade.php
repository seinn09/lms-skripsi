<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\StaffProdi;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public User $user;
    public Collection $studyPrograms;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string|min:8|confirmed')]
    public string $password = '';
    public string $password_confirmation = '';

    #[Rule('nullable|string')]
    public string $nip = '';

    #[Rule('required|exists:study_programs,id')]
    public int $study_program_id;

    public function mount(User $user): void
    {
        $this->user = $user;
        
        $this->studyPrograms = StudyProgram::orderBy('name')->get();

        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->staffProdi) {
            $this->nip = $user->staffProdi->nip ?? '';
            $this->study_program_id = $user->staffProdi->study_program_id;
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|min:3',
            'study_program_id' => 'required|exists:study_programs,id',
            'password' => 'nullable|string|min:8|confirmed',
            'email' => [
                'required', 'email',
                ValidationRule::unique('users')->ignore($this->user->id),
            ],
            'nip' => [
                'nullable', 'string',
                ValidationRule::unique('staff_prodis')->ignore($this->user->staffProdi?->id),
            ]
        ]);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if (!empty($this->password)) {
            $userData['password'] = Hash::make($this->password);
        }

        $this->user->update($userData);

        $this->user->staffProdi()->updateOrCreate(
            ['user_id' => $this->user->id],
            [
                'study_program_id' => $this->study_program_id,
                'nip' => $this->nip,
            ]
        );

        session()->flash('notify', ['type' => 'success', 'message' => 'Data Staff berhasil diperbarui!']);
        $this->redirectRoute('admin.staff.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Staff Prodi: ') }} {{ $name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Data Akun & Profil</legend>

                            <label class="label" for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1" 
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="email">Email</label>
                            <input id="email" type="email" class="input w-full border-black rounded-xl m-1" 
                                   wire:model="email" />
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

                            <fieldset class="fieldset bg-blue-50 border-blue-200 rounded-box w-full border p-4">
                                <legend class="fieldset-legend text-lg font-semibold text-blue-800 bg-blue-50 px-2">Data Penugasan</legend>

                                <label class="label" for="nip">NIP (Nomor Induk Pegawai)</label>
                                <input id="nip" type="text" class="input w-full border-black rounded-xl m-1" 
                                    placeholder="Masukkan NIP Staff" 
                                    wire:model="nip" />
                                @error('nip') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                                <label class="label mt-4" for="study_program_id">Bertugas di Program Studi</label>
                                <select id="study_program_id" class="select w-full border-black rounded-xl m-1" wire:model="study_program_id">
                                    <option value="">-- Pilih Prodi --</option>
                                    @foreach ($studyPrograms as $prodi)
                                        <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                    @endforeach
                                </select>
                                @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            </fieldset>

                            
                            <div class="mt-8 flex gap-3">
                                <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                    Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.staff.index') }}" wire:navigate 
                                class="btn btn-ghost">
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