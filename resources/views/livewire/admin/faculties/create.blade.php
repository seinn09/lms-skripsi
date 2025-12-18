<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\WithTenantInput;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    use WithTenantInput;

    #[Rule('required|string|min:2')]
    public string $name = '';

    #[Rule('required|string|min:2|max:10')]
    public string $code = '';

    public function mount(): void
    {
        $this->loadTenantData();
    }

    public function save(): void
    {
        $currentTenantId = $this->getValidTenantId();

        $this->validate([
            'tenant_id' => $this->getTenantValidationRule(),
            'name' => 'required|string|min:2',
            'code' => [
                'required',
                'string',
                'min:2',
                'max:10',
                ValidationRule::unique('faculties', 'code')->where(function ($query) use ($currentTenantId) {
                    if ($currentTenantId) {
                        return $query->where('tenant_id', $currentTenantId);
                    }
                    return $query->whereNull('tenant_id');
                }),
            ],
        ]);

        DB::transaction(function () {
            Faculty::create([
                'tenant_id' => $this->getValidTenantId(),
                'name' => $this->name,
                'code' => $this->code,
            ]);
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Fakultas baru berhasil ditambahkan!']);

        $this->redirectRoute('admin.faculties.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Fakultas Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">

                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Data Fakultas</legend>

                            <x-input-tenant :tenants="$tenants_list" />

                            <label class="label" for="code">Kode Fakultas</label>
                            <input id="code" type="text" class="input w-full border-black rounded-xl m-1"
                                   placeholder="Contoh: FT, FE, FIKOM"
                                   wire:model="code" />
                            @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="name">Nama Fakultas</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1"
                                   placeholder="Contoh: Fakultas Teknik"
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </fieldset>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Fakultas Baru
                            </button>
                            <a href="{{ route('admin.faculties.index') }}" wire:navigate
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
