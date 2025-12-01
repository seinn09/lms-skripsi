<?php

use Livewire\Volt\Component;
use App\Models\Exam;
use App\Models\Question;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public Exam $exam;
    public Collection $availableQuestions;
    public array $selectedQuestions = [];

    public string $search = '';
    public string $typeFilter = '';

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->loadAvailableQuestions();
    }

    public function updatedSearch() { $this->loadAvailableQuestions(); }
    public function updatedTypeFilter() { $this->loadAvailableQuestions(); }

    public function loadAvailableQuestions(): void
    {
        $existingQuestionIds = $this->exam->questions()->pluck('questions.id');

        $this->availableQuestions = Question::where('course_id', $this->exam->week->course_id)
            ->whereNotIn('id', $existingQuestionIds)
            ->orderBy('created_at', 'desc')
            ->search($this->search)
            ->type($this->typeFilter)
            ->get();
    }

    public function addSelected(): void
    {
        $idsToAdd = array_filter($this->selectedQuestions);
        
        if (empty($this->selectedQuestions)) {
            return;
        }

        DB::transaction(function () {
            $currentMaxOrder = $this->exam->questions()->max('order') ?? 0;

            foreach ($this->selectedQuestions as $questionId) {
                $currentMaxOrder++;
                $this->exam->questions()->attach($questionId, [
                    'order' => $currentMaxOrder
                ]);
            }
        });

        session()->flash('notify', [
            'type' => 'success',
            'message' => count($this->selectedQuestions) . ' soal berhasil ditambahkan ke ujian!'
        ]);

        $this->redirectRoute('exams.questions', $this->exam, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Ambil dari Bank Soal: {{ $exam->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold">Pilih Soal</h3>
                        <p class="text-sm text-gray-500">
                            Menampilkan soal dari mata kuliah yang belum masuk ke ujian ini.
                        </p>
                    </div>
                    
                    <div class="flex gap-3">
                        <a href="{{ route('exams.questions', $exam) }}" wire:navigate class="btn btn-ghost">
                            Batal
                        </a>
                        
                        <button wire:click="addSelected" 
                                class="btn btn-ghost px-4 font-bold
                                            text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200"
                                @if(empty($selectedQuestions)) disabled @endif>
                            + Tambahkan Terpilih (<span x-text="$wire.selectedQuestions.length"></span>)
                        </button>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row gap-4 bg-base-100 p-1 mb-3">
                    <div class="form-control flex-1">
                        <div class="input-group">
                            <input type="text" 
                                   wire:model.live.debounce.300ms="search" 
                                   placeholder="Cari pertanyaan..." 
                                   class="input input-bordered w-full border-black rounded-xl m-1" />
                        </div>
                    </div>

                    <div class="form-control w-full md:w-48">
                        <select wire:model.live="typeFilter" class="select select-bordered w-full border-black rounded-xl m-1">
                            <option value="">Semua Tipe</option>
                            <option value="multiple_choice">Pilihan Ganda</option>
                            <option value="essay">Esai</option>
                        </select>
                    </div>
                </div>
                        

                <div class="overflow-x-auto border rounded-lg">
                    <table class="table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="w-10">
                                    </th>
                                <th>Pertanyaan</th>
                                <th class="w-24 text-center">Tipe</th>
                                <th class="w-20 text-center">Bobot</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($availableQuestions as $question)
                                <tr class="hover:bg-gray-50 cursor-pointer" 
                                    onclick="document.getElementById('check-{{ $question->id }}').click()">
                                    
                                    <td class="text-center">
                                        <input type="checkbox" 
                                               id="check-{{ $question->id }}" 
                                               value="{{ $question->id }}"
                                               wire:model.live="selectedQuestions"
                                               class="checkbox checkbox-primary checkbox-sm" 
                                               onclick="event.stopPropagation()" /> 
                                               </td>
                                    
                                    <td>
                                        <div class="font-medium">
                                            {{ $question->question_text }}
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        @if($question->type == 'multiple_choice')
                                            <span class="badge badge-info badge-sm">PG</span>
                                        @else
                                            <span class="badge badge-warning badge-sm">Esai</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center font-mono">
                                        {{ $question->weight }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">
                                        <p class="font-bold">Tidak ada soal yang tersedia.</p>
                                        <p class="text-sm">Mungkin semua soal sudah masuk ke ujian ini, atau Bank Soal masih kosong.</p>
                                        <a href="{{ route('questions.create', ['course' => $exam->week->course_id]) }}" 
                                           wire:navigate
                                           class="btn btn-link btn-sm mt-2">
                                            Buat Soal Baru di Bank Soal
                                        </a>
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