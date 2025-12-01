<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Course $course;
    public Collection $questions;
    public ?int $questionToDelete = null;

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->loadQuestions();
    }

    public function loadQuestions(): void
    {
        $this->questions = $this->course->questions()
                                ->orderBy('created_at', 'desc')
                                ->get();
    }

    public function confirmDeleteQuestion(int $id): void
    {
        $this->questionToDelete = $id;
        $this->js("
            Swal.fire({
                title: 'Hapus soal permanen?',
                text: 'Soal ini akan dihapus dari Bank Soal dan SEMUA ujian yang menggunakannya!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, hapus permanen!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) { \$wire.destroyQuestion(); }
            })
        ");
    }

    public function destroyQuestion(): void
    {
        if ($this->questionToDelete) {
            Question::destroy($this->questionToDelete);
            $this->questionToDelete = null;
            session()->flash('notify', ['type' => 'success', 'message' => 'Soal berhasil dihapus dari Bank Soal.']);
            $this->loadQuestions();
        }
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Bank Soal: {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h3 class="text-lg font-bold">Daftar Pertanyaan</h3>
                        <p class="text-sm text-gray-500">
                            Total Soal: {{ $questions->count() }}
                        </p>
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('courses.detail', $course) }}" wire:navigate class="btn btn-ghost btn-sm">
                            &larr; Kembali ke Detail
                        </a>

                        @permission('questions-create')
                            <a href="{{ route('questions.create', $course) }}" wire:navigate class="btn btn-primary btn-sm text-white">
                                + Buat Soal Baru
                            </a>
                        @endpermission
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
                                        @endif
                                    </td>
                                    <td class="text-center font-mono">{{ $question->weight }}</td>
                                    <td class="flex justify-center gap-2">
                                        @permission('questions-update')
                                            <a href="{{ route('questions.edit', $question) }}" wire:navigate class="btn bg-yellow-500 px-2 font-bold
                                            text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-yellow-600">Edit</a>
                                        @endpermission
                                        
                                        @permission('questions-delete')
                                            <button class="btn bg-red-500 px-2 font-bold
                                            text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-600" 
                                                    wire:click="confirmDeleteQuestion({{ $question->id }})">
                                                Hapus
                                            </button>
                                        @endpermission
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-8 text-gray-500">
                                        Bank Soal masih kosong.
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