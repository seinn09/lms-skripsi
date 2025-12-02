<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

new class extends Component
{
    public CourseClass $class;
    public Collection $lecturers;

    #[Rule('required|string|max:5')]
    public string $class_suffix = ''; 

    #[Rule('required|string')]
    public string $semester = '';

    #[Rule('required|integer|min:1')]
    public int $capacity = 40;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null; 
    
    #[Rule('required|in:open,closed')]
    public string $status = 'open';

    #[Rule('required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu')]
    public string $day = 'Senin';

    #[Rule('required|date_format:H:i')]
    public string $time_start = '';

    #[Rule('required|date_format:H:i|after:time_start')]
    public string $time_end = '';

    public function mount(CourseClass $courseClass): void
    {
        $this->class = $courseClass;
        $course = $courseClass->course;

        $this->class_suffix = Str::afterLast($courseClass->class_code, '-');

        $this->semester = $courseClass->semester;
        $this->capacity = $courseClass->capacity;
        $this->user_id = $courseClass->user_id;
        $this->status = $courseClass->status;
        $this->day = $courseClass->day ?? 'Senin';

        $this->time_start = $courseClass->time_start ? date('H:i', strtotime($courseClass->time_start)) : '';
        $this->time_end = $courseClass->time_end ? date('H:i', strtotime($courseClass->time_end)) : '';

        $prodiId = $course->study_program_id;
        $this->lecturers = User::whereHas('pengajar', function ($q) use ($prodiId) {
            $q->where('study_program_id', $prodiId);
        })->orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();

        $courseCode = $this->class->course->course_code;
        $fullClassCode = $courseCode . '-' . strtoupper($this->class_suffix);

        $exists = CourseClass::where('class_code', $fullClassCode)
                             ->where('id', '!=', $this->class->id)
                             ->exists();
        
        if ($exists) {
            $this->addError('class_suffix', "Kelas $fullClassCode sudah ada.");
            return;
        }

        $this->class->update([
            'user_id' => $this->user_id,
            'class_code' => $fullClassCode,
            'semester' => $this->semester,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'day' => $this->day,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
        ]);

        session()->flash('notify', ['type' => 'success', 'message' => 'Informasi kelas diperbarui!']);
        
        $this->redirectRoute('courses.detail', $this->class->course_id, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Kelas: {{ $class->class_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form wire:submit="save">
                    
                    <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                        <legend class="fieldset-legend text-lg font-semibold">Edit Penawaran Kelas</legend>

                        <label class="label" for="class_suffix">Nama Kelas (Suffix)</label>
                        <div class="join w-full">
                            <button class="btn border-black rounded-s-lg bg-gray-200 no-animation" disabled>{{ $class->course->course_code }} - </button>
                            <input id="class_suffix" type="text" 
                                   class="input input-bordered w-full border-black rounded-e-lg uppercase" 
                                   wire:model="class_suffix" />
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Hasil: {{ $class->course->course_code }}-{{ strtoupper($class_suffix) }}</span>
                        </label>
                        @error('class_suffix') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="label" for="semester">Semester</label>
                                <input id="semester" type="text" class="input w-full border-black rounded-xl m-1 input-bordered" 
                                       wire:model="semester" />
                                @error('semester') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label" for="status">Status Pendaftaran</label>
                                <select id="status" class="select w-full border-black rounded-xl m-1 input-bordered" wire:model="status">
                                    <option value="open">Dibuka</option>
                                    <option value="closed">Ditutup</option>
                                </select>
                                @error('status') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="label" for="day">Hari</label>
                                <select id="day" class="select w-full border-black rounded-xl m-1 input-bordered" wire:model="day">
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                                @error('day') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label" for="time_start">Jam Mulai</label>
                                <input id="time_start" type="time" class="input w-full border-black rounded-xl m-1 input-bordered" wire:model="time_start" />
                                @error('time_start') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label" for="time_end">Jam Selesai</label>
                                <input id="time_end" type="time" class="input w-full border-black rounded-xl m-1 input-bordered" wire:model="time_end" />
                                @error('time_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="label" for="capacity">Kapasitas</label>
                                <input id="capacity" type="number" class="input w-full border-black rounded-xl m-1 input-bordered" wire:model="capacity" min="1" />
                                @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="label" for="user_id">Dosen Pengampu</label>
                                <select id="user_id" class="select w-full border-black rounded-xl m-1 input-bordered" wire:model="user_id">
                                    @foreach ($lecturers as $dosen)
                                        <option value="{{ $dosen->id }}">{{ $dosen->name }}</option>
                                    @endforeach
                                </select>
                                @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-600 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-700 px-8">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('courses.detail', $class->course_id) }}" wire:navigate 
                               class="btn btn-ghost font-bold
                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200 px-4">
                                Batal
                            </a>
                        </div>

                    </fieldset>
                </form>

            </div>
        </div>
    </div>
</div>