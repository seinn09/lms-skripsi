<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\WithTenantInput;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    use WithTenantInput;

    public Department $department;
    public array $faculties = [];

    #[Rule('required|string|min:2')]
    public string $name = '';

    #[Rule('required|string|min:2|max:10')]
    public string $code = '';

    #[Rule('required|exists:faculties,id')]
    public ?int $faculty_id = null;

    public function mount(Department $department): void
    {
        $this->department = $department;
        $this->loadTenantData();

        $this->name = $department->name;
        $this->code = $department->code;
        $this->faculty_id = $department->faculty_id;
        $this->tenant_id = $department->tenant_id;

        $this->loadFaculties();
    }

    public function updatedTenantId(): void
    {
        $this->loadFaculties();
        $this->faculty_id = null;
    }

    protected function loadFaculties(): void
    {
        if ($this->isSuperAdmin()) {
            if (!empty($this->tenant_id)) {
                $this->faculties = Faculty::where('tenant_id', $this->tenant_id)
                    ->orderBy('name')
                    ->get()
                    ->all();
            } else {
                $this->faculties = [];
            }
        } else {
            $this->faculties = Faculty::orderBy('name')->get()->all();
        }
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
                ValidationRule::unique('departments', 'code')->where(function ($query) use ($currentTenantId) {
                    if ($currentTenantId) {
                        return $query->where('tenant_id', $currentTenantId);
                    }
                    return $query->whereNull('tenant_id');
                })->ignore($this->department->id),
            ],
            'faculty_id' => [
                'required',
                ValidationRule::exists('faculties', 'id')->where(function ($query) use ($currentTenantId) {
                    return $query->where('tenant_id', $currentTenantId);
                }),
            ],
        ]);

        DB::transaction(function () {
            $this->department->update([
                'tenant_id' => $this->getValidTenantId(),
                'name' => $this->name,
                'code' => $this->code,
                'faculty_id' => $this->faculty_id,
            ]);
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Data departemen berhasil diperbarui!']);

        $this->redirectRoute('admin.departments.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Departemen: ') . $department->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">

                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Data Departemen</legend>

                            <x-input-tenant :tenants="$tenants_list" />

                            <label class="label mt-4" for="faculty_id">Fakultas</label>
                            <select id="faculty_id" class="select w-full border-black rounded-xl m-1" wire:model.live="faculty_id">
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                @endforeach
                            </select>
                            @error('faculty_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="code">Kode Departemen</label>
                            <input id="code" type="text" class="input w-full border-black rounded-xl m-1"
                                   wire:model="code" />
                            @error('code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="name">Nama Departemen</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1"
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </fieldset>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.departments.index') }}" wire:navigate
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
