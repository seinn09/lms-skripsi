<?php

use Livewire\Volt\Component;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

new class extends Component
{
    public Assignment $assignment;
    
    // Properti untuk Siswa
    public Collection $mySubmissions;

    // Properti untuk Dosen
    public Collection $classes;

    public function mount(Assignment $assignment): void
    {
        $this->assignment = $assignment;
        $user = Auth::user();

        if ($user->hasRole('siswa')) {
            $this->loadStudentData();
        } else {
            $this->loadTeacherData($user);
        }
    }

    // Logic untuk Siswa (Sama seperti sebelumnya)
    public function loadStudentData(): void
    {
        $this->mySubmissions = $this->assignment->submissions()
                                    ->where('user_id', Auth::id())
                                    ->get();
    }

    // Logic untuk Dosen/Admin (BARU)
    public function loadTeacherData($user): void
    {
        // 1. Ambil Course dari Assignment ini
        $course = $this->assignment->week->course;

        // 2. Ambil Kelas-kelas yang terhubung
        if ($user->hasRole(['superadministrator', 'admin'])) {
            // Admin lihat semua kelas
            $this->classes = $course->courseClasses()
                                ->withCount('students') // Hitung total siswa
                                ->orderBy('class_code', 'asc')
                                ->get();
        } else {
            // Pengajar hanya lihat kelas yang DIA ajar
            $this->classes = $course->courseClasses()
                                ->where('user_id', $user->id)
                                ->withCount('students')
                                ->orderBy('class_code', 'asc')
                                ->get();
        }
    }
    
    public function confirmRemove(): void
    {
        $this->js("
            Swal.fire({
                title: 'Hapus Submisi?',
                text: 'Semua file jawaban Anda untuk tugas ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.removeSubmission();
                }
            })
        ");
    }

    public function removeSubmission(): void
    {
        foreach ($this->mySubmissions as $submission) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->delete();
        }

        session()->flash('notify', ['type' => 'success', 'message' => 'Submisi berhasil dihapus.']);
        $this->loadSubmissions();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $assignment->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="mb-8 border-b pb-6 flex justify-between items-start gap-4">
    
                    <div class="flex-1">
                        <h1 class="text-3xl font-bold mb-2">{{ $assignment->title }}</h1>
                        
                        <div class="prose max-w-none text-gray-600">
                            @if($assignment->description)
                                <p>{{ $assignment->description }}</p>
                            @else
                                <p class="italic text-gray-400">Tidak ada instruksi khusus.</p>
                            @endif
                        </div>
                    </div>

                    <a href="{{ route('courses.materials.show', ['course' => $assignment->week->course, 'week' => $assignment->week]) }}" 
                        wire:navigate 
                        class="btn btn-ghost shrink-0">
                            &larr; Kembali
                    </a>

                </div>

                @role('siswa')
                    <h3 class="text-xl font-bold mb-4">Submission status</h3>

                    <div class="overflow-hidden border border-gray-300 rounded-lg">
                        <table class="w-full text-sm text-left">
                            <tbody>
                                
                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 bg-gray-50 w-1/4 font-bold text-gray-700">Submission status</th>
                                    <td class="py-3 px-4 {{ $mySubmissions->isNotEmpty() ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                        @if ($mySubmissions->isNotEmpty())
                                            Submitted for grading
                                        @else
                                            No attempt
                                        @endif
                                    </td>
                                </tr>

                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 bg-gray-50 font-bold text-gray-700">Grading status</th>
                                    <td class="py-3 px-4 bg-gray-50">
                                        @php
                                            $gradedSubmission = $mySubmissions->whereNotNull('grade')->first();
                                        @endphp

                                        @if ($gradedSubmission)
                                            <span class="font-bold text-blue-600">Graded ({{ $gradedSubmission->grade }})</span>
                                        @else
                                            Not graded
                                        @endif
                                    </td>
                                </tr>

                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 bg-gray-50 font-bold text-gray-700">Due date</th>
                                    <td class="py-3 px-4 bg-gray-50">
                                        @if ($assignment->deadline)
                                            {{ $assignment->deadline->format('l, d F Y, h:i A') }}
                                        @else
                                            No Deadline
                                        @endif
                                    </td>
                                </tr>

                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 bg-gray-50 font-bold text-gray-700">Time remaining</th>
                                    <td class="py-3 px-4 
                                        {{ ($assignment->deadline && now()->gt($assignment->deadline)) ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                        @if ($assignment->deadline)
                                            @if ($mySubmissions->isNotEmpty())
                                                <span class="text-green-700">Assignment was submitted {{ $mySubmissions->first()->created_at->diffForHumans($assignment->deadline) }}</span>
                                            @else
                                                @if (now()->gt($assignment->deadline))
                                                    Assignment is overdue by: {{ $assignment->deadline->diffForHumans() }}
                                                @else
                                                    {{ $assignment->deadline->diffForHumans(null, true) }} remaining
                                                @endif
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>

                                <tr class="border-b border-gray-200">
                                    <th class="py-3 px-4 bg-gray-50 font-bold text-gray-700">Last modified</th>
                                    <td class="py-3 px-4 bg-gray-50">
                                        @if ($mySubmissions->isNotEmpty())
                                            {{ $mySubmissions->last()->updated_at->format('l, d F Y, h:i A') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>

                                <tr>
                                    <th class="py-3 px-4 bg-gray-50 font-bold text-gray-700 align-top">File submissions</th>
                                    <td class="py-3 px-4 bg-gray-50">
                                        @if ($mySubmissions->isNotEmpty())
                                            <ul class="space-y-2">
                                                @foreach ($mySubmissions as $sub)
                                                    <li class="flex items-center gap-2">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        
                                                        @if ($sub->file_path)
                                                            @php
                                                                $fullName = basename($sub->file_path);
                                                                $cleanName = \Illuminate\Support\Str::after($fullName, '_');
                                                            @endphp

                                                            <a href="{{ Storage::url($sub->file_path) }}" 
                                                            download="{{ $cleanName }}" 
                                                            class="text-blue-600 hover:underline">
                                                                {{ $cleanName }}
                                                            </a>
                                                        @else
                                                            <span>{{ $sub->text_content }}</span>
                                                        @endif

                                                        <span class="text-xs text-gray-500 ml-2">
                                                            {{ $sub->created_at->format('d F Y, h:i A') }}
                                                        </span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                    <div class="mt-8">
                            <h3 class="text-xl font-bold mb-4">Submission comments</h3>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                @php
                                    $feedback = $mySubmissions->first()->comment_sub ?? null;
                                @endphp

                                @if ($feedback)
                                    <div class="flex gap-3">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                                        </svg>
                                        <div>
                                            <p class="font-bold text-gray-800">Feedback dari Dosen:</p>
                                            <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $feedback }}</p>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-gray-500 italic">Belum ada komentar atau feedback dari dosen.</p>
                                @endif
                            </div>
                        </div>

                    <div class="mt-8 flex justify-center gap-4">
                        @if ($mySubmissions->isNotEmpty())
                            <a href="{{ route('assignments.submission', $assignment) }}" wire:navigate class="btn btn-md bg-yellow-500 font-bold rounded-lg
                                text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-400">
                                Edit submission
                            </a>
                            
                            <button wire:click="confirmRemove" class="btn px-3 bg-red-500 font-bold rounded-lg 
                                text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-600">
                                Remove submission
                            </button>
                        @else
                            <a href="{{ route('assignments.submission', $assignment) }}" wire:navigate class="btn btn-md bg-blue-500 font-bold rounded-lg 
                                text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                                Add submission
                            </a>
                        @endif
                    </div>

                @else
                    <div class="flex flex-wrap gap-4 mb-8">
                        <div class="bg-gray-100 rounded-lg px-4 py-2 border border-gray-200">
                            <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Due Date</span>
                            <p class="text-sm font-semibold text-gray-800">
                                {{ $assignment->deadline ? $assignment->deadline->format('l, d F Y, h:i A') : 'No Deadline' }}
                            </p>
                        </div>

                        @if($assignment->deadline)
                            <div class="bg-gray-100 rounded-lg px-4 py-2 border border-gray-200">
                                <span class="text-xs text-gray-500 uppercase font-bold tracking-wider">Time Remaining</span>
                                <p class="text-sm font-bold {{ now()->gt($assignment->deadline) ? 'text-red-600' : 'text-green-600' }}">
                                    @if(now()->gt($assignment->deadline))
                                        Overdue by {{ $assignment->deadline->diffForHumans() }}
                                    @else
                                        {{ $assignment->deadline->diffForHumans(null, true) }} left
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <h3 class="text-xl font-bold mb-4">Daftar Kelas (Grading)</h3>
                    
                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Kode Kelas</th>
                                    <th>Semester</th>
                                    <th>Total Mahasiswa</th>
                                    <th>Submisi Masuk</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $class)
                                    <tr class="hover:bg-base-300">
                                        <td class="font-bold">{{ $class->class_code }}</td>
                                        <td>{{ $class->semester }}</td>
                                        <td>{{ $class->students_count }} Mahasiswa</td>
                                        <td>
                                            @php
                                                $submittedCount = \App\Models\AssignmentSubmission::where('assignment_id', $assignment->id)
                                                    ->whereIn('user_id', $class->students()->pluck('users.id'))
                                                    ->distinct('user_id')
                                                    ->count('user_id');
                                            @endphp
                                            <span class="badge {{ $submittedCount > 0 ? 'badge-primary' : 'badge-ghost' }}">
                                                {{ $submittedCount }} / {{ $class->students_count }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.assignments.grading.class', ['assignment' => $assignment, 'class' => $class]) }}" 
                                               wire:navigate
                                               class="btn btn-sm bg-blue-300 font-bold rounded-lg 
                                text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-400">
                                                Lihat Submisi
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center font-bold">Anda tidak mengampu kelas apapun untuk mata kuliah ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endrole

            </div>
        </div>
    </div>
</div>