<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Course $course;
    public Collection $lecturers;

    #[Rule('required|string|max:5')]
    public string $class_suffix = '';

    #[Rule('required|string')]
    public string $semester = 'Ganjil 2025/2026';

    #[Rule('required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu')]
    public string $day = 'Senin';

    #[Rule('required|date_format:H:i')]
    public string $time_start = '';

    #[Rule('required|date_format:H:i|after:time_start')]
    public string $time_end = '';

    #[Rule('required|integer|min:1')]
    public int $capacity = 40;

    #[Rule('required|exists:users,id')]
    public ?int $user_id = null;

    public function mount(Course $course): void
    {
        $this->course = $course;
        
        $prodiId = $this->course->study_program_id;

        $this->lecturers = User::whereHas('pengajar', function ($q) use ($prodiId) {
            $q->where('study_program_id', $prodiId);
        })->orderBy('name')->get();
    }

    public function save(): void
    {
        $this->validate();

        $fullClassCode = $this->course->course_code . '-' . strtoupper($this->class_suffix);

        if (CourseClass::where('class_code', $fullClassCode)->exists()) {
            $this->addError('class_suffix', "Kelas $fullClassCode sudah ada.");
            return;
        }
        
        // dd($this->course->id, $this->user_id, $fullClassCode, $this->semester, $this->day, $this->time_start, $this->time_end, $this->capacity);

        CourseClass::create([
            'course_id' => $this->course->id,
            'user_id' => $this->user_id,
            'class_code' => $fullClassCode,
            'semester' => $this->semester,
            'day' => $this->day,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
            'capacity' => $this->capacity,
            'status' => 'open',
        ]);

        session()->flash('notify', ['type' => 'success', 'message' => 'Kelas baru berhasil ditambahkan!']);
        
        $this->redirectRoute('courses.detail', $this->course, navigate: true);
        $this->redirectRoute('courses.detail', $this->course, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tambah Kelas: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                <form wire:submit="save">
                    
                    <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                        <legend class="fieldset-legend text-lg font-semibold">Detail Penawaran Kelas</legend>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="label font-bold">Mata Kuliah</label>
                                <input type="text" class="input w-full border-black rounded-xl m-1 bg-gray-100" value="{{ $course->name }}" disabled />
                            </div>
                            <div>
                                <label class="label font-bold">Kode MK</label>
                                <input type="text" class="input w-full border-black rounded-xl m-1 bg-gray-100" value="{{ $course->course_code }}" disabled />
                            </div>
                        </div>

                        <label class="label" for="class_suffix">Nama Kelas (Suffix)</label>
                        <div class="join w-full">
                            <button class="btn join-item no-animation">{{ $course->course_code }} - </button>
                            <input id="class_suffix" type="text" 
                                   class="input w-full border-black rounded-xl m-1 uppercase" 
                                   placeholder="A / B / C / MALAM" 
                                   wire:model="class_suffix" />
                        </div>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Hasil Kode: {{ $course->course_code }}-{{ strtoupper($class_suffix) }}</span>
                        </label>
                        @error('class_suffix') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <label class="label mt-4" for="semester">Semester</label>
                        <input id="semester" type="text" class="input border-black rounded-xl m-1 w-full" 
                               placeholder="Cth: Ganjil 2025/2026" 
                               wire:model="semester" />
                        @error('semester') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            
                            <div>
                                <label class="label" for="day">Hari</label>
                                <select id="day" class="select border-black rounded-xl m-1 w-full input-bordered" wire:model="day">
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
                                <input id="time_start" type="time" class="input border-black rounded-xl m-1 w-full input-bordered" 
                                       wire:model="time_start" />
                                @error('time_start') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="label" for="time_end">Jam Selesai</label>
                                <input id="time_end" type="time" class="input border-black rounded-xl m-1 w-full input-bordered" 
                                       wire:model="time_end" />
                                @error('time_end') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <label class="label mt-4" for="capacity">Kapasitas Mahasiswa</label>
                        <input id="capacity" type="number" class="input border-black rounded-xl m-1 w-full" 
                               wire:model="capacity" min="1" />
                        @error('capacity') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <label class="label mt-4" for="user_id">Dosen Pengampu</label>
                        <select id="user_id" class="select border-black rounded-xl m-1 w-full" wire:model="user_id">
                            <option value="">-- Pilih Dosen --</option>
                            @foreach ($lecturers as $dosen)
                                <option value="{{ $dosen->id }}">{{ $dosen->name }} ({{ $dosen->pengajar->nip ?? '-' }})</option>
                            @endforeach
                        </select>
                        <label class="label">
                            <span class="label-text-alt text-gray-500">Hanya menampilkan dosen dari prodi yang sama dengan mata kuliah ini.</span>
                        </label>
                        @error('user_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn bg-blue-600 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-700 px-8">
                                Simpan Kelas
                            </button>
                            <a href="{{ route('courses.detail', $course) }}" wire:navigate 
                               class="btn btn-ghost px-4 font-bold
                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                                Batal
                            </a>
                        </div>

                    </fieldset>
                </form>

            </div>
        </div>
    </div>
</div>