<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public Question $question;

    #[Rule('required|in:multiple_choice,essay')]
    public string $type = '';

    #[Rule('required|string|min:5')]
    public string $question_text = '';

    #[Rule('required|numeric|min:1')]
    public int $weight = 5;
    
    public array $options = [];

    public function mount(Question $question): void
    {
        $this->question = $question;

        $this->type = $question->type;
        $this->question_text = $question->question_text;
        $this->weight = $question->weight;

        if ($question->type === 'multiple_choice') {
            foreach ($question->options as $opt) {
                $this->options[] = [
                    'text' => $opt->option_text,
                    'is_correct' => (bool) $opt->is_correct,
                ];
            }
        }

        if (empty($this->options)) {
            $this->addOption();
            $this->addOption();
        }
    }

    public function addOption(): void
    {
        $this->options[] = ['text' => '', 'is_correct' => false];
    }

    public function removeOption($index): void
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    public function setCorrectOption($index): void
    {
        foreach ($this->options as $key => $option) {
            $this->options[$key]['is_correct'] = ($key === $index);
        }
    }

    public function save(): void
    {
        $this->validate();

        if ($this->type === 'multiple_choice') {
            $filledOptions = array_filter($this->options, fn($o) => !empty($o['text']));
            if (count($filledOptions) < 2) {
                $this->addError('options', 'Minimal harus ada 2 pilihan jawaban yang diisi.');
                return;
            }
            $hasCorrect = collect($this->options)->contains('is_correct', true);
            if (!$hasCorrect) {
                $this->addError('options', 'Anda harus memilih satu kunci jawaban yang benar.');
                return;
            }
        }

        DB::transaction(function () {
            $this->question->update([
                'type' => $this->type,
                'question_text' => $this->question_text,
                'weight' => $this->weight,
            ]);

            if ($this->type === 'multiple_choice') {
                $this->question->options()->delete();

                foreach ($this->options as $opt) {
                    if (!empty($opt['text'])) {
                        Option::create([
                            'question_id' => $this->question->id,
                            'option_text' => $opt['text'],
                            'is_correct' => $opt['is_correct'],
                        ]);
                    }
                }
            } else {
                $this->question->options()->delete();
            }
        });

        session()->flash('notify', ['type' => 'success', 'message' => 'Soal berhasil diperbarui!']);

        $exam = $this->question->exams()->first();
        $this->redirectRoute('exams.questions', $exam, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Soal
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form wire:submit="save">
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-2">
                            <label class="label font-bold">Tipe Soal</label>
                            <div class="flex gap-4">
                                <label class="label cursor-pointer gap-2 border rounded-lg px-4 py-2 hover:bg-gray-50">
                                    <input type="radio" wire:model.live="type" value="multiple_choice" class="radio radio-primary" />
                                    <span class="label-text font-semibold">Pilihan Ganda</span>
                                </label>
                                <label class="label cursor-pointer gap-2 border rounded-lg px-4 py-2 hover:bg-gray-50">
                                    <input type="radio" wire:model.live="type" value="essay" class="radio radio-warning" />
                                    <span class="label-text font-semibold">Esai (Uraian)</span>
                                </label>
                            </div>
                            @error('type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="label font-bold">Bobot Nilai</label>
                            <input type="number" wire:model="weight" class="input input-bordered w-full border-black rounded-xl m-1" min="1" />
                            @error('weight') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mb-6">
                        <label class="label font-bold">Pertanyaan</label>
                        <textarea wire:model="question_text" rows="4" 
                                  class="textarea textarea-bordered w-full border-black rounded-xl m-1 text-lg" 
                                  placeholder="Tulis pertanyaan Anda di sini..."></textarea>
                        @error('question_text') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    @if($type === 'multiple_choice')
                        <div class="bg-blue-50 p-6 rounded-xl border border-blue-100 mb-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="font-bold text-blue-800">Pilihan Jawaban</h3>
                                <button type="button" wire:click="addOption" class="btn btn-xs btn-outline btn-primary">
                                    + Tambah Opsi
                                </button>
                            </div>

                            @error('options') 
                                <div class="alert alert-error text-sm mb-4 py-2">{{ $message }}</div> 
                            @enderror

                            <div class="space-y-3">
                                @foreach($options as $index => $option)
                                    <div class="flex items-center gap-3">
                                        <div class="tooltip" data-tip="Tandai sebagai Kunci Jawaban">
                                            <input type="radio" name="correct_option" 
                                                   class="radio radio-success"
                                                   wire:click="setCorrectOption({{ $index }})"
                                                   {{ $option['is_correct'] ? 'checked' : '' }} />
                                        </div>

                                        <div class="flex-1">
                                            <input type="text" 
                                                   wire:model="options.{{ $index }}.text"
                                                   class="input input-bordered w-full border-black rounded-xl m-1 {{ $option['is_correct'] ? 'input-success border-2' : '' }}" 
                                                   placeholder="Pilihan {{ chr(65 + $index) }}" />
                                        </div>

                                        <button type="button" wire:click="removeOption({{ $index }})" 
                                                class="btn btn-square btn-ghost btn-sm text-red-500"
                                                {{ count($options) <= 2 ? 'disabled' : '' }}>
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="flex gap-3 mt-8 border-t pt-6">
                        <button type="submit" class="btn px-2 bg-blue-600 px-4 by-1 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-700">
                            Simpan Perubahan
                        </button>
                        
                        @php
                            $exam = $question->exams()->first();
                        @endphp
                        <a href="{{ $exam ? route('exams.questions', $exam) : '#' }}" wire:navigate 
                           class="btn px-2 btn-ghost px-4 by-1 font-bold
                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                            Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>