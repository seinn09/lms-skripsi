<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Pengajar;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    public User $user;
    
    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string')]
    public string $alamat = '';

    #[Rule('nullable|date')]
    public $tanggal_lahir;

    #[Rule('nullable|string|unique:pengajars,nip')]
    public string $nip = '';

    #[Rule('nullable|string|unique:siswas,nisn')]
    public string $nisn = '';

    public function mount(User $user): void
    {
        $this->user = $user->loadMissing(['pengajar', 'siswa']);

        $this->name = $user->name;
        $this->email = $user->email;

        if ($user->label === 'pengajar' && $user->pengajar) {
            $this->alamat = $user->pengajar->alamat;
            $this->tanggal_lahir = $user->pengajar->tanggal_lahir;
            $this->nip = $user->pengajar->nip;
        } elseif ($user->label === 'siswa' && $user->siswa) {
            $this->alamat = $user->siswa->alamat;
            $this->tanggal_lahir = $user->siswa->tanggal_lahir;
            $this->nisn = $user->siswa->nisn;
        }
    }

    public function save(): void
    {
        $this->validate([
            'email' => [
                'required',
                'email',
                ValidationRule::unique('users')->ignore($this->user->id),
            ],
            'nip' => [
                'nullable',
                'string',
                ValidationRule::unique('pengajars')->ignore($this->user->pengajar?->id),
            ],
            'nisn' => [
                'nullable',
                'string',
                ValidationRule::unique('siswas')->ignore($this->user->siswa?->id),
            ],
        ]);

        $this->validate([
            'name' => 'required|string|min:3',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
        ]);
        
        DB::transaction(function () {
            $this->user->update([
                'name' => $this->name,
                'email' => $this->email,
            ]);

            $profileData = [
                'alamat' => $this->alamat,
                'tanggal_lahir' => $this->tanggal_lahir,
            ];

            if ($this->user->label === 'pengajar') {
                $profileData['nip'] = $this->nip;
                $this->user->pengajar()->updateOrCreate(['user_id' => $this->user->id], $profileData);

            } elseif ($this->user->label === 'siswa') {
                $profileData['nisn'] = $this->nisn;
                $this->user->siswa()->updateOrCreate(['user_id' => $this->user->id], $profileData);
            }
        });

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Data anda berhasil diubah!'
        ]);

        $this->redirectRoute('admin.pengguna.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pengguna: ') }} {{ $name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Akun</legend>

                            <label class="label" for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl" 
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="email">Email</label>
                            <input id="email" type="email" class="input w-full border-black rounded-xl" 
                                   wire:model="email" />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="role">Role</label>
                            <input id="role" type="text" class="input w-full border-black rounded-xl bg-gray-100" 
                                   value="{{ $user->roles->first()?->display_name }}" disabled />
                            <span class="text-xs text-gray-500">Mengubah role belum didukung di halaman edit ini.</span>

                        </fieldset>

                        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Profil</legend>

                            @if ($user->label === 'pengajar')
                                <label class="label" for="nip">NIP (Nomor Induk Pegawai)</label>
                                <input id="nip" type="text" class="input w-full border-black rounded-xl" 
                                       placeholder="Masukkan NIP" 
                                       wire:model="nip" />
                                @error('nip') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            @elseif ($user->label === 'siswa')
                                <label class="label" for="nisn">NISN (Nomor Induk Siswa Nasional)</label>
                                <input id="nisn" type="text" class="input w-full border-black rounded-xl" 
                                       placeholder="Masukkan NISN" 
                                       wire:model="nisn" />
                                @error('nisn') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            @endif

                            <label class="label mt-4" for="alamat">Alamat</label>
                            <textarea id="alamat" class="textarea w-full border-black rounded-xl" 
                                      placeholder="Masukkan alamat"
                                      wire:model="alamat"></textarea>
                            @error('alamat') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="tanggal_lahir">Tanggal Lahir</label>
                            <input id="tanggal_lahir" type="date" class="input w-full border-black rounded-xl" 
                                   wire:model="tanggal_lahir" />
                            @error('tanggal_lahir') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </fieldset>
                        
                       <div class="mt-6 flex gap-3">
                           <button type="submit" class="btn bg-blue-500 px-4 font-bold
                            text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                               Simpan
                           </button>
                           <a href="{{ route('admin.pengguna.index') }}" wire:navigate 
                              class="btn btn-ghost bg-gray-200 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300">
                               Batal
                           </a>
                       </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>