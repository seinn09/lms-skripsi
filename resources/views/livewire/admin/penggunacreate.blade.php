<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Pengajar;
use App\Models\Siswa;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

new class extends Component
{
    public Collection $roles;

    #[Rule('required|min:3')]
    public string $name = '';

    #[Rule('required|min:3')]
    public string $email = '';

    #[Rule('required|confirmed|min:8')]
    public string $password = '';

    #[Rule('required|min:8')]
    public string $password_confirmation = '';

    #[Rule('required|exists:roles,id')]
    public int $role_id;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole('superadministrator')) {
            $this->roles = Role::whereIn('name', ['admin', 'pengajar', 'siswa'])->get();
        } elseif ($user->hasRole('admin')) {
            $this->roles = Role::whereIn('name', ['pengajar', 'siswa'])->get();
        } else {
            $this->roles = collect();
        }
        
        if ($this->roles->isNotEmpty()) {
            $this->role_id = $this->roles->first()->id;
        }
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () {
            
            $selectedRole = Role::find($this->role_id);

            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'label' => $selectedRole->name, 
            ]);

            $user->addRole($selectedRole);

            if ($selectedRole->name === 'pengajar') {
                Pengajar::create([
                    'user_id' => $user->id
                ]);
            } elseif ($selectedRole->name === 'siswa') {
                Siswa::create([
                    'user_id' => $user->id
                ]);
            }

        });

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Data berhasil disimpan!'
        ]);

        $this->redirectRoute('admin.pengguna.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Pengguna Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-200 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Akun Pengguna</legend>

                            <label class="label" for="name">Nama Lengkap</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Nama Pengguna" 
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="email">Email</label>
                            <input id="email" type="email" class="input w-full border-black rounded-xl" 
                                   placeholder="email@example.com"
                                   wire:model="email" />
                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="password">Password</label>
                            <input id="password" type="password" class="input w-full border-black rounded-xl" 
                                   wire:model="password" />
                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="password_confirmation">Konfirmasi Password</label>
                            <input id="password_confirmation" type="password" class="input w-full border-black rounded-xl" 
                                   wire:model="password_confirmation" />
                            @error('password_confirmation') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="role_id">Role Pengguna</label>
                            <select id="role_id" class="select w-full border-black rounded-xl" wire:model="role_id">
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </select>
                            @error('role_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
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

                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>