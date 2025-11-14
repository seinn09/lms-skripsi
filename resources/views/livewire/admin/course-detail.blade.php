<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Course $course;
    public Collection $classes;
    public ?CourseClass $selectedClass = null;

   public function mount(Course $course): void
    {
        $this->course = $course->load(['owner']);
        
        $this->classes = $course->courseClasses()
            ->with('pengajar')
            ->withCount('students')
            ->get();
    }

    public function selectClass(int $classId): void
    {
        $this->selectedClass = CourseClass::with(['pengajar', 'students.siswa'])
            ->find($classId);
    }

    public function clearSelection(): void
    {
        $this->selectedClass = null;
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Mata Kuliah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">
                <h1 class="text-2xl font-bold">{{ $course->name }}</h1>
                <span class="badge badge-ghost">{{ $course->course_code }}</span>
                <p class="mt-2 text-sm text-gray-600">
                    Dosen Pengampu: {{ $course->owner->name ?? 'N/A' }}
                </p>
                <p class="mt-4">{{ $course->description }}</p>
            </div>

            <div class="p-6 bg-white shadow-sm sm:rounded-lg">

                @if ($selectedClass)

                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Detail Kelas: {{ $selectedClass->class_code }}</h2>
                        <button wire:click="clearSelection" class="btn btn-ghost">
                            &larr; Kembali ke Daftar Kelas
                        </button>
                    </div>

                    <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                        <legend class="fieldset-legend text-lg font-semibold">
                            {{ $selectedClass->semester }}
                        </legend>

                        <div class="mb-4 flex flex-col gap-2">
                            <p><strong>Dosen Pengampu:</strong> {{ $selectedClass->pengajar->name ?? 'N/A' }}</p>
                            <p><strong>Status:</strong> 
                                @if($selectedClass->status == 'open') <span class="badge badge-sm badge-success">Dibuka</span>
                                @else <span class="badge badge-sm badge-error">Ditutup</span> @endif
                            </p>
                            <p><strong>Kapasitas:</strong> {{ $selectedClass->students->count() }} / {{ $selectedClass->capacity }} Mahasiswa</p>
                        </div>

                        <h4 class="font-semibold mb-2">Mahasiswa Terdaftar ({{ $selectedClass->students->count() }}):</h4>
                        <div class="overflow-x-auto max-h-60">
                            <table class="table table-zebra table-xs">
                                <thead>
                                    <tr class="text-center">
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($selectedClass->students as $student)
                                        <tr class="text-center">
                                            <td><span class="badge badge-ghost">{{ $student->siswa->nim ?? '-' }}</span></td>
                                            <td>{{ $student->name }}</td>
                                            <td>{{ $student->email }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">Belum ada mahasiswa yang terdaftar di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </fieldset>

                @else

                    <h2 class="text-xl font-bold mb-4">Daftar Kelas yang Ditawarkan</h2>
                    <ul class="space-y-4">
                        @forelse ($classes as $class)
                            <li>
                                <a wire:click.prevent="selectClass({{ $class->id }})" href="#"
                                   class="block p-6 border rounded-lg shadow-sm hover:bg-gray-50 transition cursor-pointer">
                                    <h3 class="text-lg font-semibold">{{ $class->class_code }} ({{ $class->semester }})</h3>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Dosen: {{ $class->pengajar->name ?? 'N/A' }} | 
                                        Kapasitas: {{ $class->students_count }} / {{ $class->capacity }}
                                    </p>
                                </a>
                            </li>
                        @empty
                            <p class="text-center text-gray-500">Belum ada kelas yang ditawarkan untuk mata kuliah ini.</p>
                        @endforelse
                    </ul>
                    
                @endif
            </div>

            <div class="mt-6">
                <a href="{{ route('courses.index') }}" wire:navigate 
                   class="btn btn-ghost">
                    &larr; Kembali ke Daftar Mata Kuliah
                </a>
            </div>

        </div>
    </div>
</div>