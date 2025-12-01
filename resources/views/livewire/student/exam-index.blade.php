<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Question;
use App\Models\ExamAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

new class extends Component
{
    public Exam $exam;
    public ?ExamAttempt $attempt = null;

    public Collection $questions;
    public array $answers = [];
    public $endTime;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;

        $this->attempt = ExamAttempt::where('exam_id', $this->exam->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($this->attempt && $this->attempt->status == 'in_progress') {
            $this->loadExamData();
        }
    }

    public function startAttempt(): void
    {
        $now = now();
        if ($this->exam->start_time && $now->lt($this->exam->start_time)) {
            $this->addError('access', 'Ujian belum dibuka.'); return;
        }
        if ($this->exam->end_time && $now->gt($this->exam->end_time)) {
            $this->addError('access', 'Ujian sudah ditutup.'); return;
        }

        $this->attempt = ExamAttempt::create([
            'exam_id' => $this->exam->id,
            'user_id' => Auth::id(),
            'started_at' => $now,
            'status' => 'in_progress'
        ]);

        $this->loadExamData();
    }

    public function loadExamData(): void
    {
        $this->endTime = $this->attempt->started_at->addMinutes($this->exam->duration_minutes)->timestamp;

        $this->questions = $this->exam->questions()->with('options')->get();

        $existingAnswers = ExamAnswer::where('exam_attempt_id', $this->attempt->id)->get();

        foreach ($existingAnswers as $ans) {
            if ($ans->selected_option_id) {
                $this->answers[$ans->question_id] = $ans->selected_option_id;
            } elseif ($ans->answer_text) {
                $this->answers[$ans->question_id] = $ans->answer_text;
            }
        }
    }

    public function saveAnswer($questionId, $value): void
    {
        $question = $this->questions->find($questionId);
        if (!$question) return;

        $data = [
            'exam_attempt_id' => $this->attempt->id,
            'question_id' => $questionId,
        ];

        if ($question->type === 'multiple_choice') {
            $data['selected_option_id'] = $value;
            $data['answer_text'] = null;
        } else {
            $data['selected_option_id'] = null;
            $data['answer_text'] = $value;
        }

        ExamAnswer::updateOrCreate(
            ['exam_attempt_id' => $this->attempt->id, 'question_id' => $questionId],
            $data
        );
    }

    public function confirmSubmit(): void
    {
        $this->js("
            Swal.fire({
                title: 'Kumpulkan Jawaban?',
                text: 'Apakah Anda yakin ingin mengakhiri ujian ini? Jawaban tidak dapat diubah setelah dikumpulkan.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Kumpulkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    \$wire.submitExam();
                }
            })
        ");
    }

    public function submitExam(): void
    {
        $totalScore = 0;
        $answers = ExamAnswer::where('exam_attempt_id', $this->attempt->id)->get();

        foreach ($answers as $ans) {
            if ($ans->selected_option_id) {
                $option = \App\Models\Option::find($ans->selected_option_id);
                $question = $this->questions->find($ans->question_id);
                
                if ($option && $option->is_correct) {
                    $totalScore += $question->weight;
                    $ans->update(['is_correct' => true, 'score_obtained' => $question->weight]);
                } else {
                    $ans->update(['is_correct' => false, 'score_obtained' => 0]);
                }
            }
        }

        $this->attempt->update([
            'finished_at' => now(),
            'status' => 'completed',
            'total_score' => $totalScore
        ]);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

                @if (! $attempt)
                    
                    <div class="text-center py-10">
                        <h1 class="text-3xl font-bold mb-4">{{ $exam->title }}</h1>
                        
                        @if($exam->description)
                            <div class="prose max-w-none mx-auto mb-8 text-gray-600">
                                <p>{{ $exam->description }}</p>
                            </div>
                        @endif

                        <div class="flex justify-center gap-6 mb-10">
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-100 w-48">
                                <span class="block text-xs font-bold text-blue-800 uppercase tracking-wide">Durasi</span>
                                <span class="text-2xl font-bold text-blue-600">{{ $exam->duration_minutes }} Menit</span>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg border border-green-100 w-48">
                                <span class="block text-xs font-bold text-green-800 uppercase tracking-wide">Jumlah Soal</span>
                                <span class="text-2xl font-bold text-green-600">
                                    {{ $exam->questions()->count() }} Soal
                                </span>
                            </div>
                        </div>

                        <div class="mb-8 text-sm text-gray-500">
                            @if($exam->start_time)
                                <p>Dibuka: <strong>{{ $exam->start_time->format('d M Y, H:i') }}</strong></p>
                            @endif
                            @if($exam->end_time)
                                <p>Ditutup: <strong>{{ $exam->end_time->format('d M Y, H:i') }}</strong></p>
                            @endif
                        </div>

                        @error('access')
                            <div class="alert alert-error max-w-md mx-auto mb-6">
                                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror

                        @php
                            $now = now();
                            $isOpen = true;
                            if ($exam->start_time && $now->lt($exam->start_time)) $isOpen = false;
                            if ($exam->end_time && $now->gt($exam->end_time)) $isOpen = false;
                        @endphp

                        @if($isOpen)
                            <button wire:click="startAttempt" 
                                    wire:loading.attr="disabled"
                                    class="btn bg-blue-500 font-bold text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600 px-10">
                                Mulai Mengerjakan
                            </button>
                            <div class="mt-2 text-xs text-gray-400">
                                Waktu akan berjalan segera setelah tombol diklik.
                            </div>
                        @else
                            <button class="btn btn-disabled btn-lg px-10" disabled>
                                Ujian Tidak Tersedia
                            </button>
                        @endif

                    </div>

                @elseif ($attempt->status == 'in_progress')
                    
                    <div class="sticky top-0 z-20 bg-white border-b shadow-sm pb-4 mb-8 -mx-6 px-6 pt-4"
                         x-data="{ 
                             endTime: {{ $endTime }}, 
                             now: Math.floor(Date.now() / 1000),
                             remaining: 0,
                             hasNotified: false,
                             
                             init() {
                                 this.updateTimer();

                                 setInterval(() => {
                                     this.updateTimer();
                                 }, 1000);
                             },

                             updateTimer() {
                                 this.now = Math.floor(Date.now() / 1000);
                                 this.remaining = this.endTime - this.now;

                                 if (this.remaining <= 180 && this.remaining > 0 && !this.hasNotified) {
                                     this.hasNotified = true;
                                     
                                     Swal.fire({
                                         title: 'Waktu Tinggal 3 Menit!',
                                         text: 'Segera periksa kembali jawaban Anda sebelum waktu habis.',
                                         icon: 'warning',
                                         timer: 3000,
                                         timerProgressBar: true,
                                         showConfirmButton: true,
                                         confirmButtonText: 'Oke, Mengerti'
                                     });
                                 }
                                 
                                 if (this.remaining <= 0) {
                                     if (this.remaining > -5) { 
                                         $wire.submitExam();
                                     }
                                 }
                             },

                             get formattedTime() {
                                 if (this.remaining <= 0) return '00:00:00';
                                 let hours = Math.floor(this.remaining / 3600);
                                 let minutes = Math.floor((this.remaining % 3600) / 60);
                                 let seconds = this.remaining % 60;
                                 
                                 return `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                             }
                         }">
                        
                        <div class="flex justify-between items-center">
                            <h2 class="text-lg font-bold text-gray-700">Lembar Jawaban</h2>
                            
                            <div class="flex items-center gap-2 px-4 py-2 bg-gray-100 rounded-lg border border-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                <span class="font-mono text-xl font-bold text-red-600" x-text="formattedTime">Loading...</span>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-8">
                        @foreach ($questions as $index => $q)
                            <div class="p-4 border rounded-lg bg-gray-50 hover:bg-white hover:shadow-md transition duration-200">
                                
                                <div class="flex gap-3 mb-4">
                                    <span class="flex-shrink-0 flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-600 font-bold text-sm">
                                        {{ $index + 1 }}
                                    </span>
                                    <div class="prose max-w-none text-gray-800 font-medium">
                                        {{ $q->question_text }}
                                    </div>
                                    <span class="ml-auto badge badge-ghost badge-sm">
                                        {{ $q->weight }} Poin
                                    </span>
                                </div>

                                <div class="pl-11">
                                    @if ($q->type === 'multiple_choice')
                                        <div class="space-y-2">
                                            @foreach ($q->options as $opt)
                                                <label class="flex items-center gap-3 cursor-pointer p-2 rounded hover:bg-blue-50 transition border border-transparent hover:border-blue-200">
                                                    <input type="radio" 
                                                           name="q_{{ $q->id }}" 
                                                           value="{{ $opt->id }}"
                                                           wire:model="answers.{{ $q->id }}"
                                                           wire:change="saveAnswer({{ $q->id }}, {{ $opt->id }})"
                                                           class="radio radio-primary radio-sm" />
                                                    <span class="text-gray-700">{{ $opt->option_text }}</span>
                                                </label>
                                            @endforeach
                                        </div>

                                    @elseif ($q->type === 'essay')
                                        <textarea 
                                            rows="4" 
                                            wire:model.lazy="answers.{{ $q->id }}"
                                            wire:blur="saveAnswer({{ $q->id }}, $event.target.value)"
                                            class="textarea textarea-bordered w-full bg-white"
                                            placeholder="Ketik jawaban Anda di sini..."></textarea>
                                        <p class="text-xs text-gray-500 mt-1">* Jawaban tersimpan otomatis saat Anda klik di luar kotak.</p>
                                    @endif
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="mt-10 border-t pt-6 text-center">
                        <button wire:click="confirmSubmit"
                                class="btn bg-blue-500 font-bold text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600 px-10">
                            Kumpulkan Jawaban
                        </button>
                    </div>

                @else
                    
                    <div class="text-center py-12">
                        
                        <div class="mb-6">
                            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-green-100">
                                <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                        </div>

                        <h1 class="text-3xl font-bold text-gray-900 mb-2">Ujian Selesai!</h1>
                        <p class="text-gray-500 mb-10">Jawaban Anda telah berhasil dikirim dan disimpan.</p>

                        <div class="max-w-xl mx-auto bg-base-100 border border-base-300 rounded-xl shadow-sm overflow-hidden mb-10">
                            <div class="bg-base-200 px-6 py-4 border-b border-base-300">
                                <h3 class="font-bold text-gray-700">Hasil Pengerjaan</h3>
                            </div>
                            
                            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-center">
                                
                                <div>
                                    <span class="block text-sm font-bold text-gray-400 uppercase tracking-wider">Total Nilai</span>
                                    <span class="block text-5xl font-extrabold text-primary mt-2">
                                        {{ floatval($attempt->total_score) }}
                                    </span>
                                    <span class="text-xs text-gray-400">Poin</span>
                                </div>

                                <div class="flex flex-col justify-center gap-2 text-sm">
                                    @php
                                        // Hitung statistik sederhana dari attempt ini
                                        $totalQ = $exam->questions()->count();
                                        $correct = $attempt->answers()->where('is_correct', true)->count();
                                        // Asumsi: Soal Esai (is_correct null) dianggap belum dinilai
                                        $pending = $attempt->answers()->whereNull('is_correct')->count();
                                    @endphp

                                    <div class="flex justify-between border-b border-gray-100 pb-1">
                                        <span class="text-gray-600">Jumlah Soal:</span>
                                        <span class="font-bold">{{ $totalQ }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-gray-100 pb-1">
                                        <span class="text-green-600">Jawaban Benar (PG):</span>
                                        <span class="font-bold text-green-600">{{ $correct }}</span>
                                    </div>
                                    @if($pending > 0)
                                        <div class="flex justify-between border-b border-gray-100 pb-1">
                                            <span class="text-orange-500">Menunggu Penilaian (Esai):</span>
                                            <span class="font-bold text-orange-500">{{ $pending }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            @if($pending > 0)
                                <div class="bg-yellow-50 px-6 py-3 text-xs text-yellow-700 text-left">
                                    * Nilai akhir mungkin berubah setelah dosen menilai jawaban esai Anda.
                                </div>
                            @endif
                        </div>

                        <a href="{{ route('courses.materials.show', ['course' => $exam->week->course_id, 'week' => $exam->week_id]) }}" 
                           wire:navigate
                           class="btn btn-outline btn-primary">
                            &larr; Kembali ke Materi
                        </a>

                    </div>

                @endif

            </div>
        </div>
    </div>
</div>