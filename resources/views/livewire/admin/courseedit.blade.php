<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Course;
use App\Models\User;
use App\Models\StudyProgram;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule as ValidationRule;

new class extends Component
{
    public Course $course;
    public Collection $pengajars;
    public Collection $studyPrograms;

    #[Rule('required|string|min:5')]
    public string $course_code = '';

    #[Rule('required|string|min:3')]
    public string $name = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|exists:study_programs,id')]
    public ?int $study_program_id = null;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null;

    public function mount(Course $course): void
    {
        $this->course = $course;

        $this->course_code = $course->course_code;
        $this->name = $course->name;
        $this->description = $course->description;
        $this->study_program_id = $course->study_program_id;
        $this->user_id = $course->user_id;

        $this->studyPrograms = StudyProgram::orderBy('name')->get();
        $this->loadPengajars();
    }

    public function updatedStudyProgramId($value)
    {
        $this->loadPengajars();
        $this->user_id = null;
    }

    public function loadPengajars(): void
    {
        if (empty($this->study_program_id)) {
            $this->pengajars = new Collection();
            return;
        }

        $this->pengajars = User::whereHas('roles', function ($query) {
            $query->where('name', 'pengajar');
        })
        ->whereHas('pengajar', function ($query) {
            $query->where('study_program_id', $this->study_program_id);
        })
        ->orderBy('name', 'asc')
        ->get();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'course_code' => [
                'required',
                'string',
                'min:5',
                ValidationRule::unique('courses')->ignore($this->course->id), 
            ],
            'name' => 'required|string|min:3',
            'description' => 'nullable|string',
            'study_program_id' => 'required|exists:study_programs,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // Additional validation: verify lecturer belongs to selected study program
        $lecturer = User::whereHas('pengajar', function ($query) use ($validated) {
            $query->where('study_program_id', $validated['study_program_id']);
        })->find($validated['user_id']);

        if (!$lecturer) {
            $this->addError('user_id', 'Dosen yang dipilih tidak terdaftar di Program Studi yang dipilih.');
            return;
        }

        $this->course->update($validated);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Mata kuliah berhasil diperbarui!'
        ]);

        $this->redirectRoute('courses.index', navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Mata Kuliah: ') }} {{ $name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Mata Kuliah</legend>

                            <label class="label" for="course_code">Kode Mata Kuliah</label>
                            <input id="course_code" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Cth: NINFUM6039" 
                                   wire:model="course_code" />
                            @error('course_code') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label" for="name">Nama Mata Kuliah</label>
                            <input id="name" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Contoh: Pemrograman Web Lanjut" 
                                   wire:model="name" />
                            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="description">Deskripsi</label>
                            <textarea id="description" class="textarea w-full border-black rounded-xl" 
                                      placeholder="Deskripsi singkat mata kuliah..."
                                      wire:model="description"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="study_program_id">Program Studi</label>
                            <select id="study_program_id" class="select w-full border-black rounded-xl" wire:model.live="study_program_id">
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach ($studyPrograms as $prodi)
                                    <option value="{{ $prodi->id }}">{{ $prodi->name }} ({{ $prodi->degree }})</option>
                                @endforeach
                            </select>
                            @error('study_program_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="user_id">Dosen Pengampu (PJ Mata Kuliah)</label>
                            <select id="user_id" class="select w-full border-black rounded-xl" wire:model="user_id" @if($pengajars->isEmpty()) disabled @endif>
                                @if($pengajars->isEmpty())
                                    <option value="">-- Pilih Program Studi terlebih dahulu --</option>
                                @else
                                    @foreach ($pengajars as $pengajar)
                                        <option value="{{ $pengajar->id }}">{{ $pengajar->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <div class="mt-6 flex gap-3">
                                <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                    Simpan
                                </button>
                                <a href="{{ route('courses.index') }}" wire:navigate 
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