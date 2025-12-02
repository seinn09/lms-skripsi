<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use App\Models\Assignment;
use App\Models\Exam;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Course $course;
    
    public Collection $classes;
    public ?CourseClass $selectedClass = null;

    public Collection $gradableItems;
    public Collection $students;

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        $user = Auth::user();

        if ($user->hasRole(['superadministrator', 'admin'])) {
            $this->classes = $this->course->courseClasses()
                                ->withCount('students')
                                ->orderBy('class_code', 'asc')
                                ->get();
        } else {
            $this->classes = $this->course->courseClasses()
                                ->where('user_id', $user->id)
                                ->withCount('students')
                                ->orderBy('class_code', 'asc')
                                ->get();
        }
    }

    public function selectClass(int $classId): void
    {
        $this->selectedClass = CourseClass::find($classId);

        $assignments = Assignment::whereHas('week', fn($q) => $q->where('course_id', $this->course->id))->get();
        $exams = Exam::whereHas('week', fn($q) => $q->where('course_id', $this->course->id))->get();

        $assignments->each(fn($item) => $item->type = 'assignment');
        $exams->each(fn($item) => $item->type = 'exam');

        $this->gradableItems = $assignments->concat($exams)->sortBy('created_at');

        $this->students = $this->selectedClass->students()
            ->with([
                'siswa',
                'submissions' => function ($q) {
                    $q->whereIn('assignment_id', $this->gradableItems->where('type', 'assignment')->pluck('id'));
                },
                'examAttempts' => function ($q) {
                    $q->whereIn('exam_id', $this->gradableItems->where('type', 'exam')->pluck('id'));
                }
            ])
            ->orderBy('name', 'asc')
            ->get();
    }

    public function clearSelection(): void
    {
        $this->selectedClass = null;
        $this->reset(['students', 'gradableItems']);
    }

    public function getGrade($student, $item)
    {
        if ($item->type === 'assignment') {
            $submission = $student->submissions->firstWhere('assignment_id', $item->id);
            return $submission ? ($submission->grade ?? '-') : null;
        } 
        elseif ($item->type === 'exam') {
            $attempt = $student->examAttempts->firstWhere('exam_id', $item->id);
            return $attempt ? ($attempt->total_score ?? '-') : null;
        }
        return null;
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buku Nilai: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8"> <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if ($selectedClass)
                    
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h1 class="text-xl font-bold">
                                Rekap Nilai: {{ $selectedClass->class_code }}
                            </h1>
                            <p class="text-sm text-gray-500">
                                {{ $students->count() }} Mahasiswa | {{ $gradableItems->count() }} Aktivitas
                            </p>
                        </div>
                        <div class="flex gap-3">
                            <button wire:click="clearSelection" class="py-2 px-4 text-base rounded-md text-black btn-ghost
                                                transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                                &larr; Kembali ke Daftar Kelas
                            </button>
                            <button class="py-2 px-4 text-base rounded-md text-black bg-green-400
                                 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-green-500">
                                Export Excel
                            </button>

                            @permission('submissions-update')
                                <a href="{{ route('classes.finalize', $selectedClass) }}" wire:navigate 
                                   class="py-2 px-4 text-base rounded-md text-black bg-blue-500
                                 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                                    Finalisasi Nilai Akhir
                                </a>
                            @endpermission
                        </div>
                    </div>

                    <div class="overflow-x-auto border rounded-lg max-h-[70vh]">
                        <table class="table table-xs table-pin-rows table-pin-cols">
                            <thead>
                                <tr>
                                    <th class="bg-base-200 z-10 w-64">Mahasiswa</th>
                                    <th class="bg-base-200 w-32">NIM</th>

                                    @foreach ($gradableItems as $item)
                                        <th class="text-center min-w-[100px]">
                                            <div class="flex flex-col items-center gap-1" title="{{ $item->title }}">
                                                @if($item->type == 'assignment')
                                                    <span class="badge badge-xs badge-error text-white">Tgs</span>
                                                @else
                                                    <span class="badge badge-xs badge-warning text-black">Uji</span>
                                                @endif
                                                <span class="truncate w-24 text-center block font-normal">
                                                    {{ \Illuminate\Support\Str::limit($item->title, 12, '..') }}
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $student)
                                    <tr class="hover:bg-base-50">
                                        <th class="bg-white font-normal border-r">
                                            <div class="font-bold text-gray-900 truncate w-60" title="{{ $student->name }}">
                                                {{ $student->name }}
                                            </div>
                                        </th>
                                        <td class="border-r font-mono text-xs">{{ $student->siswa->nim ?? '-' }}</td>

                                        @foreach ($gradableItems as $item)
                                            @php $grade = $this->getGrade($student, $item); @endphp
                                            <td class="text-center border-r p-0">
                                                <div class="w-full h-full py-2 flex items-center justify-center 
                                                    {{ $grade === null ? 'bg-gray-50' : '' }}">
                                                    @if ($grade !== null)
                                                        <span class="font-mono font-bold {{ $grade == '-' ? 'text-gray-300' : 'text-blue-600' }}">
                                                            {{ $grade }}
                                                        </span>
                                                    @else
                                                        <span class="text-gray-300 text-xs">-</span>
                                                    @endif
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $gradableItems->count() + 2 }}" class="text-center py-8 text-gray-500">
                                            Belum ada mahasiswa di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                @else

                    <div class="flex justify-between items-center mb-6">
                        <h1 class="text-xl font-bold">Pilih Kelas untuk Melihat Nilai</h1>
                        <a href="{{ route('courses.index') }}" wire:navigate class="btn btn-ghost btn-sm">
                            Kembali ke Menu
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @forelse ($classes as $class)
                            <div wire:click="selectClass({{ $class->id }})" 
                                 class="card bg-base-100 border border-gray-200 shadow-sm hover:shadow-md hover:border-primary transition cursor-pointer group">
                                <div class="card-body p-6">
                                    <h2 class="card-title text-lg group-hover:text-primary transition">
                                        {{ $class->class_code }}
                                    </h2>
                                    <p class="text-sm text-gray-500 mb-4">{{ $class->semester }}</p>
                                    
                                    <div class="flex justify-between items-end">
                                        <div class="text-xs text-gray-400">
                                            Mahasiswa
                                        </div>
                                        <div class="text-2xl font-bold text-gray-700">
                                            {{ $class->students_count }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-10 text-gray-500 bg-gray-50 rounded-lg">
                                <p class="font-bold">Tidak ada kelas yang tersedia.</p>
                                <p class="text-sm">Anda belum ditugaskan ke kelas manapun di mata kuliah ini.</p>
                            </div>
                        @endforelse
                    </div>

                @endif

            </div>
        </div>
    </div>
</div>