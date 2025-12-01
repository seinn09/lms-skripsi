<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Exam $exam;
    public Collection $questions;

    public ?int $questionToDelete = null;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->loadQuestions();
    }

    public function loadQuestions(): void
    {
        $this->questions = $this->exam->questions()
                                ->orderByPivot('order', 'asc')
                                ->get();
    }

    public function confirmDetachQuestion(int $id): void
    {
        $this->questionToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus soal ini dari ujian?',
                text: 'Soal akan dihapus dari ujian ini, tapi masih tersimpan di Bank Soal.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { \$wire.detachQuestion(); }
            })
        ");
    }

    public function detachQuestion(): void
    {
        if ($this->questionToDelete) {
            $this->exam->questions()->detach($this->questionToDelete);
            $this->questionToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Soal berhasil dihapus dari ujian.']);
            $this->loadQuestions();
        }
    }

}; ?>

<div>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Kelola Soal: {{ $exam->title }}
            </h2>
            
            <a href="{{ route('courses.materials.show', ['course' => $exam->week->course_id, 'week' => $exam->week_id]) }}" 
               wire:navigate 
               class="btn btn-sm btn-ghost">
                &larr; Kembali ke Materi
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-bold">Daftar Pertanyaan</h3>
                        <p class="text-sm text-gray-500">
                            Total Soal: {{ $questions->count() }} | 
                            Total Bobot: {{ $questions->sum('weight') }} Poin |
                            Total Durasi: {{ $exam->duration_minutes }} Menit
                        </p>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ route('questions.create', ['course' => $exam->week->course_id, 'from_exam' => $exam->id]) }}" wire:navigate class="btn px-4 bg-blue-500 font-bold
                            text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600"> + Buat Soal Baru
                        </a>
                        
                        <a href="{{ route('exams.questions.bank', $exam) }}" class="btn px-4 bg-green-500 font-bold
                            text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-green-600">
                            Ambil dari Bank Soal
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto border rounded-lg">
                    <table class="table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-10">No</th>
                                <th>Pertanyaan</th>
                                <th class="w-24 text-center">Tipe</th>
                                <th class="w-20 text-center">Bobot</th>
                                <th class="w-32 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($questions as $index => $question)
                                <tr class="hover:bg-gray-50">
                                    <td class="font-bold text-center">{{ $index + 1 }}</td>
                                    <td>
                                        <div class="line-clamp-2 font-medium">
                                            {{ $question->question_text }}
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($question->type == 'multiple_choice')
                                            <span class="badge badge-info badge-sm">PG</span>
                                        @elseif($question->type == 'essay')
                                            <span class="badge badge-warning badge-sm">Esai</span>
                                        @else
                                            <span class="badge badge-ghost badge-sm">{{ $question->type }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-mono">{{ $question->weight }}</td>
                                    <td class="flex justify-center gap-2">
                                        <a href="{{ route('questions.edit', ['question' => $question->id, 'from_exam' => $exam->id]) }}" class="btn px-2 bg-yellow-500 font-bold
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">Edit</a href="{{ route('questions.edit', $question) }}">

                                        <button class="btn px-2 bg-red-600 font-bold
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-700" 
                                                wire:click="confirmDetachQuestion({{ $question->id }})">
                                            Hapus
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        Belum ada soal di ujian ini. Silakan buat soal baru.
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