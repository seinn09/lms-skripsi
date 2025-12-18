<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Tenant;

new class extends Component
{
    // PK Manual
    #[Rule('required|string|unique:tenants,tenant_id|alpha_dash')]
    public string $tenant_id = '';

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('nullable|email')]
    public string $email = '';

    #[Rule('nullable|string')]
    public string $address = '';

    public function save(): void
    {
        $this->validate();

        Tenant::create([
            'tenant_id' => $this->tenant_id, // Simpan ID manual
            'slug' => $this->tenant_id,      // Slug samakan dengan ID
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        session()->flash('notify', ['type' => 'success', 'message' => 'Kampus baru berhasil dibuat!']);
        $this->redirectRoute('superadmin.tenants.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Kampus Baru
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form wire:submit="save">
                    <fieldset class="fieldset bg-base-100 border p-4 rounded-box">
                        <legend class="fieldset-legend text-lg font-bold">Data Kampus</legend>

                        <label class="label font-bold">Kode Unik Kampus (ID)</label>
                        <input type="text" wire:model="tenant_id"
                               class="input input-bordered w-full border-black rounded-xl font-mono"
                               placeholder="Cth: univ-merdeka (Tanpa spasi)" />
                        <span class="text-xs text-gray-500">Digunakan sebagai ID database. Hanya huruf, angka, dan strip.</span>
                        @error('tenant_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <label class="label font-bold mt-4">Nama Kampus</label>
                        <input type="text" wire:model="name" class="input input-bordered w-full border-black rounded-xl" placeholder="Universitas..." />
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <label class="label font-bold mt-4">Email Resmi</label>
                        <input type="email" wire:model="email" class="input input-bordered w-full border-black rounded-xl" />

                        <label class="label font-bold mt-4">Alamat</label>
                        <textarea wire:model="address" class="textarea textarea-bordered w-full border-black rounded-xl"></textarea>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('superadmin.tenants.index') }}" wire:navigate class="py-2 px-4 text-base rounded-md btn-ghost text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">Batal</a>
                            <button type="submit" class="py-2 px-4 text-base rounded-md bg-blue-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">Simpan</button>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>
    </div>
</div>
