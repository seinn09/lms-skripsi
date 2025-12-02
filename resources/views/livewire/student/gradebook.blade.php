<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Assignment;
use App\Models\Exam;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Course $course;
    
    public array $grades = []; 

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->loadMyGrades();
    }

    public function loadMyGrades(): void
    {
        $userId = Auth::id();

        $assignments = Assignment::whereHas('week', fn($q) => $q->where('course_id', $this->course->id))
            ->with(['submissions' => fn($q) => $q->where('user_id', $userId)])
            ->get()
            ->map(function ($item) {
                $mySub = $item->submissions->first();
                return [
                    'type' => 'Tugas',
                    'title' => $item->title,
                    'due_date' => $item->deadline ? $item->deadline->format('d M Y, H:i') : '-',
                    'submitted_at' => $mySub ? $mySub->submitted_at->format('d/m/y H:i') : null,
                    'grade' => $mySub ? $mySub->grade : null,
                    'feedback' => $mySub ? $mySub->comment_sub : null,
                    'link' => route('assignments.show', $item),
                ];
            });

        $exams = Exam::whereHas('week', fn($q) => $q->where('course_id', $this->course->id))
            ->with(['attempts' => fn($q) => $q->where('user_id', $userId)])
            ->get()
            ->map(function ($item) {
                $myAttempt = $item->attempts->first();
                return [
                    'type' => 'Ujian',
                    'title' => $item->title,
                    'due_date' => $item->end_time ? $item->end_time->format('d M Y, H:i') : '-',
                    'submitted_at' => $myAttempt && $myAttempt->finished_at ? $myAttempt->finished_at->format('d/m/y H:i') : null,
                    'grade' => $myAttempt ? $myAttempt->total_score : null,
                    'feedback' => null,
                    'link' => route('exams.attempt', $item),
                ];
            });

        $this->grades = $assignments->concat($exams)
                            ->sortBy('due_date')
                            ->values()
                            ->all();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buku Nilai: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h1 class="text-2xl font-bold">Transkrip Nilai</h1>
                        <p class="text-sm text-gray-500">
                            Berikut adalah rekapitulasi nilai tugas dan ujian Anda.
                        </p>
                    </div>
                    <a href="{{ route('courses.materials.index', $course) }}" wire:navigate 
                       class="btn btn-ghost">
                        &larr; Kembali ke Materi
                    </a>
                </div>

                <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                    <table class="table">
                        <thead>
                            <tr class="border bg-base-200 rounded-xl">
                                <th>Aktivitas</th>
                                <th>Tenggat Waktu</th>
                                <th>Status</th>
                                <th>Nilai</th>
                                <th>Umpan Balik (Feedback)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grades as $item)
                                <tr class="hover:bg-base-50">
                                    <td>
                                        <div class="flex items-center gap-3">
                                            @if($item['type'] == 'Tugas')
                                                <span class="badge badge-sm badge-error text-white">Tugas</span>
                                            @else
                                                <span class="badge badge-sm badge-warning">Ujian</span>
                                            @endif
                                            <div class="font-bold">{{ $item['title'] }}</div>
                                        </div>
                                    </td>

                                    <td>
                                        @if($item['due_date'])
                                            <div class="text-sm">{{ $item['due_date'] }}</div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item['submitted_at'])
                                            <span class="badge badge-success badge-outline gap-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-3 h-3 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                Selesai
                                            </span>
                                            <div class="text-[10px] text-gray-400 mt-1">
                                                {{ $item['submitted_at'] }} </div>
                                        @else
                                            <span class="badge badge-ghost badge-outline">Belum</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($item['grade'] !== null)
                                            <span class="font-mono text-lg font-bold text-blue-600">
                                                {{ $item['grade'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>

                                    <td class="max-w-xs">
                                        @if($item['feedback'])
                                            <p class="text-sm text-gray-600 italic">
                                                "{{ \Illuminate\Support\Str::limit($item['feedback'], 50) }}"
                                            </p>
                                        @else
                                            <span class="text-gray-300 text-xs">-</span>
                                        @endif
                                    </td>

                                    <th>
                                        <a href="{{ $item['link'] }}" wire:navigate class="btn btn-ghost btn-xs">
                                            Lihat
                                        </a>
                                    </th>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8 text-gray-500">
                                        Belum ada aktivitas penilaian di mata kuliah ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>