<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Assignment $assignment;
    public CourseClass $class;
    public Collection $students;

    public function mount(Assignment $assignment, CourseClass $class): void
    {
        $this->assignment = $assignment;
        $this->class = $class;

        $this->students = $class->students()
            ->with(['submissions' => function ($query) use ($assignment) {
                $query->where('assignment_id', $assignment->id);
            }, 'siswa'])
            ->orderBy('name', 'asc')
            ->get();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Grading: {{ $class->class_code }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">{{ $assignment->title }}</h1>
                        <p class="text-gray-500">Daftar Mahasiswa Kelas {{ $class->class_code }}</p>
                    </div>
                    <a href="{{ route('assignments.show', $assignment) }}" wire:navigate class="btn btn-ghost">
                        &larr; Kembali
                    </a>
                </div>

                <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                    <table class="table">
                        <thead>
                            <tr class="border bg-base-200 rounded-xl">
                                <th>Mahasiswa</th>
                                <th>NIM</th>
                                <th>Status Submisi</th>
                                <th>Nilai (Grade)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                @php
                                    $submission = $student->submissions->first();
                                @endphp
                                <tr class="hover:bg-base-300">
                                    <td class="font-bold">
                                        <div class="flex items-center gap-3">
                                            {{-- <div class="avatar placeholder">
                                                <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                    <span class="text-xs">{{ substr($student->name, 0, 2) }}</span>
                                                </div>
                                            </div> --}}
                                            {{ $student->name }}
                                        </div>
                                    </td>
                                    <td>{{ $student->siswa->nim ?? '-' }}</td>
                                    
                                    <td>
                                        @if ($submission)
                                            <span class="badge badge-success gap-2">
                                                Submitted
                                            </span>
                                            <div class="text-xs text-gray-500 mt-1">
                                                {{ $submission->submitted_at->diffForHumans() }}
                                            </div>
                                            @if ($assignment->deadline && $submission->submitted_at->gt($assignment->deadline))
                                                <span class="text-xs text-red-500 font-bold block">Late</span>
                                            @endif
                                        @else
                                            <span class="badge badge-ghost">Not Submit Yet</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($submission && $submission->grade !== null)
                                            <span class="font-bold text-lg text-blue-600">{{ $submission->grade }}</span> / 100
                                        @elseif ($submission)
                                            <span class="text-gray-400 italic">Perlu Dinilai</span>
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        @if ($submission)
                                            <a href="{{ route('admin.assignments.grading.student', ['assignment' => $assignment, 'class' => $class, 'student' => $student]) }}" wire:navigate 
                                            class="btn btn-sm bg-blue-500 font-bold rounded-lg 
                                            text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                                                Beri Nilai
                                            </a>
                                        @else
                                            <h2 class="italic font-bold">Not Submit Yet</h2>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center font-bold">Tidak ada mahasiswa di kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>