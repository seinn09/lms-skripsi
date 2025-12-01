<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Week;
use App\Models\Exam;

new class extends Component
{
    public Week $week;

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|integer|min:1')]
    public int $duration_minutes = 60;

    #[Rule('required|date|after_or_equal:today')]
    public ?string $start_time = null;

    #[Rule('required|date|after:start_time')]
    public ?string $end_time = null;

    public function mount(Week $week): void
    {
        $this->week = $week;
        $this->start_time = now()->addDay()->setTime(8, 0)->format('Y-m-d\TH:i');
        $this->end_time = now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i');
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['week_id'] = $this->week->id;

        Exam::create($validated);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Ujian baru berhasil dibuat! Silakan tambahkan soal.'
        ]);

        $this->redirectRoute('courses.materials.show', [
            'course' => $this->week->course_id, 
            'week' => $this->week->id
        ], navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Buat Ujian Baru: {{ $week->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <form wire:submit="save">
                    
                    <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                        <legend class="fieldset-legend text-lg font-semibold">Pengaturan Ujian</legend>

                        <label class="label" for="title">Judul Ujian</label>
                        <input id="title" type="text" class="input w-full border-black rounded-xl m-1" 
                               placeholder="Contoh: Kuis 1 - Dasar Pemrograman" 
                               wire:model="title" />
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <label class="label mt-4" for="description">Instruksi Pengerjaan</label>
                        <textarea id="description" class="textarea w-full border-black rounded-xl m-1" rows="3"
                                  placeholder="Jelaskan aturan main, sifat ujian, dll..."
                                  wire:model="description"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="label" for="duration">Durasi (Menit)</label>
                                <input id="duration" type="number" class="input w-full border-black rounded-xl m-1" 
                                       wire:model="duration_minutes" min="1" />
                                @error('duration_minutes') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="label" for="start_time">Waktu Dibuka</label>
                                <input id="start_time" type="datetime-local" class="input w-full border-black rounded-xl m-1" 
                                       wire:model="start_time" />
                                @error('start_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="label" for="end_time">Waktu Ditutup</label>
                                <input id="end_time" type="datetime-local" class="input w-full border-black rounded-xl m-1" 
                                       wire:model="end_time" />
                                @error('end_time') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-8 flex gap-3">
                            <button type="submit" class="btn btn-sm bg-blue-600 px-4 by-1 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-700">
                                Simpan & Lanjut
                            </button>
                            <a href="{{ route('courses.materials.show', ['course' => $this->week->course_id, 'week' => $this->week->id]) }}" 
                               wire:navigate 
                               class="btn btn-sm btn-ghost transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                                Batal
                            </a>
                        </div>

                    </fieldset>

                </form>
            </div>
        </div>
    </div>
</div>