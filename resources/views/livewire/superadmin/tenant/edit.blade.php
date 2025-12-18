<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Tenant;

new class extends Component
{
    public Tenant $tenant;

    // Properti untuk Form
    public string $tenant_id = ''; // Read-only

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('nullable|email')]
    public ?string $email = null; // <-- PERBAIKAN: Tambahkan '?' dan default 'null'

    #[Rule('nullable|string')]
    public ?string $address = null; // <-- PERBAIKAN: Tambahkan '?' juga di sini

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;

        // Isi form dengan data yang ada
        $this->tenant_id = $tenant->tenant_id;
        $this->name = $tenant->name;
        $this->email = $tenant->email;     // Sekarang aman jika null
        $this->address = $tenant->address; // Sekarang aman jika null
    }

    public function save(): void
    {
        $this->validate();

        // Update data tenant
        $this->tenant->update([
            'name' => $this->name,
            'email' => $this->email,
            'address' => $this->address,
        ]);

        session()->flash('notify', ['type' => 'success', 'message' => 'Data kampus berhasil diperbarui!']);
        $this->redirectRoute('superadmin.tenants.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Kampus: {{ $name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form wire:submit="save">
                    <fieldset class="fieldset bg-base-100 border p-4 rounded-box">
                        <legend class="fieldset-legend text-lg font-bold">Data Kampus</legend>

                        <label class="label font-bold">Kode Unik Kampus (ID)</label>
                        <input type="text" value="{{ $tenant_id }}"
                               class="input input-bordered w-full border-black rounded-xl font-mono bg-gray-200 text-gray-600 cursor-not-allowed"
                               disabled />
                        <span class="text-xs text-gray-500 mt-1">ID Kampus bersifat permanen dan tidak dapat diubah.</span>

                        <label class="label font-bold mt-4">Nama Kampus</label>
                        <input type="text" wire:model="name" class="input input-bordered w-full border-black rounded-xl" />
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <label class="label font-bold mt-4">Email Resmi</label>
                        <input type="email" wire:model="email" class="input input-bordered w-full border-black rounded-xl" />
                        @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <label class="label font-bold mt-4">Alamat</label>
                        <textarea wire:model="address" class="textarea textarea-bordered w-full border-black rounded-xl h-24"></textarea>
                        @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('superadmin.tenants.index') }}" wire:navigate class="py-2 px-4 text-base rounded-md text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200 btn-ghost">Batal</a>
                            <button type="submit" class="py-2 px-4 text-base rounded-md bg-yellow-500 text-black
                                                    transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">Simpan Perubahan</button>
                        </div>
                    </fieldset>
                </form>

            </div>
        </div>
    </div>
</div>
