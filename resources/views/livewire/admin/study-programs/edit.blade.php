<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\StudyProgram;
use App\Models\Department;
use App\Models\Faculty;
use Illuminate\Support\Facades\DB;
use App\Traits\Livewire\WithTenantInput;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    use WithTenantInput;

    public StudyProgram $studyProgram;
    public array $faculties = [];
    public array $departments = [];

    #[Rule('required|string|min:2')]
    public string $name = '';

    #[Rule('required|in:D3,D4,S1,S2,S3')]
    public string $degree = '';

    #[Rule('required|exists:departments,id')]
    public ?int $department_id = null;

    public ?int $faculty_id = null;

    public function mount(StudyProgram $studyProgram): void
    {
        $this->studyProgram = $studyProgram;
        $this->loadTenantData();

        $this->name = $studyProgram->name;
        $this->degree = $studyProgram->degree;
        $this->department_id = $studyProgram->department_id;
        $this->tenant_id = $studyProgram->tenant_id;

        if ($studyProgram->department) {
            $this->faculty_id = $studyProgram->department->faculty_id;
        }

        $this->loadFaculties();
        $this->loadDepartments();
    }

    public function updatedTenantId(): void
    {
        $this->loadFaculties();
        $this->faculty_id = null;
        $this->department_id = null;
        $this->departments = [];
    }

    public function updatedFacultyId(): void
    {
        $this->loadDepartments();
        $this->department_id = null;
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

    protected function loadDepartments(): void
    {
        if (!empty($this->faculty_id)) {
            $currentTenantId = $this->getValidTenantId();

            $this->departments = Department::where('faculty_id', $this->faculty_id)
                ->where('tenant_id', $currentTenantId)
                ->orderBy('name')
                ->get()
                ->all();
        } else {
            $this->departments = [];
        }
    }

    public function save(): void
    {
        $currentTenantId = $this->getValidTenantId();

        $this->validate([
            'tenant_id' => $this->getTenantValidationRule(),
            'name' => 'required|string|min:2',
            'degree' => 'required|in:D3,D4,S1,S2,S3',
            'department_id' => [
                'required',
                ValidationRule::exists('departments', 'id')->where(function ($query) use ($currentTenantId) {
                    return $query->where('tenant_id', $currentTenantId);
                }),
            ],
        ]);

        DB::transaction(function () {
            $this->studyProgram->update([
                'tenant_id' => $this->getValidTenantId(),
                'name' => $this->name,
                'degree' => $this->degree,
                'department_id' => $this->department_id,
            ]);
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Data program studi berhasil diperbarui!']);

        $this->redirectRoute('admin.study-programs.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Program Studi: ') . $studyProgram->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">

                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4 mb-6">
                            <legend class="fieldset-legend text-lg font-semibold">Data Program Studi</legend>

                            <x-input-tenant :tenants="$tenants_list" />

                            <label class="label mt-4" for="faculty_id">Fakultas</label>
                            <select id="faculty_id" class="select w-full border-black rounded-xl m-1" wire:model.live="faculty_id">
                                <option value="">-- Pilih Fakultas --</option>
                                @foreach($faculties as $faculty)
                                    <option value="{{ $faculty->id }}">{{ $faculty->name }} ({{ $faculty->code }})</option>
                                @endforeach
                            </select>

                            <label class="label mt-4" for="department_id">Departemen</label>
                            <select id="department_id" class="select w-full border-black rounded-xl m-1" wire:model="department_id">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }} ({{ $dept->code }})</option>
                                @endforeach
                            </select>
                            @error('department_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="name">Nama Program Studi</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl m-1"
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="degree">Jenjang</label>
                            <select id="degree" class="select w-full border-black rounded-xl m-1" wire:model="degree">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="D3">D3 (Diploma 3)</option>
                                <option value="D4">D4 (Diploma 4)</option>
                                <option value="S1">S1 (Sarjana)</option>
                                <option value="S2">S2 (Magister)</option>
                                <option value="S3">S3 (Doktor)</option>
                            </select>
                            @error('degree') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        </fieldset>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.study-programs.index') }}" wire:navigate
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
