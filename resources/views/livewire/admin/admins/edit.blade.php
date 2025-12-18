<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\Livewire\WithTenantInput;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    use WithTenantInput;

    public User $user;

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('nullable|string|min:8|confirmed')]
    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        $this->user = $user;

        if (!$user->hasRole('admin')) {
            abort(403, 'User ini bukan Admin');
        }

        $this->loadTenantData();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->tenant_id = $user->tenant_id;
    }

    public function save(): void
    {
        $currentTenantId = $this->getValidTenantId();

        $this->validate([
            'tenant_id' => $this->getTenantValidationRule(),
            'name' => 'required|string|min:3',
            'email' => [
                'required',
                'email',
                ValidationRule::unique('users')->where(function ($query) use ($currentTenantId) {
                    return $query->where('tenant_id', $currentTenantId);
                })->ignore($this->user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        DB::transaction(function () {
            $userData = [
                'name' => $this->name,
                'email' => $this->email,
                'tenant_id' => $this->getValidTenantId(),
            ];

            if (!empty($this->password)) {
                $userData['password'] = Hash::make($this->password);
            }

            $this->user->update($userData);
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Data admin berhasil diperbarui!']);

        $this->redirectRoute('admin.admins.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Admin: ') . $user->name }}
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
                        </fieldset>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.admins.index') }}" wire:navigate
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
