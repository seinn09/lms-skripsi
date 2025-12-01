<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Exam;
use App\Models\Week;

new class extends Component
{
    public Exam $exam;
    public Week $week; // Untuk navigasi kembali

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('required|integer|min:1')]
    public int $duration_minutes = 0;

    #[Rule('required|date')]
    public ?string $start_time = null;

    #[Rule('required|date|after:start_time')]
    public ?string $end_time = null;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->week = $exam->week; // Simpan relasi week

        // Isi form dengan data yang ada
        $this->title = $exam->title;
        $this->description = $exam->description;
        $this->duration_minutes = $exam->duration_minutes;

        // Format datetime agar bisa dibaca input 'datetime-local' (Y-m-d\TH:i)
        $this->start_time = $exam->start_time ? $exam->start_time->format('Y-m-d\TH:i') : null;
        $this->end_time = $exam->end_time ? $exam->end_time->format('Y-m-d\TH:i') : null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        // Update data di database
        $this->exam->update($validated);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Informasi ujian berhasil diperbarui!'
        ]);

        // Redirect kembali ke halaman "Detail Pertemuan" (Page 2)
        $this->redirectRoute('courses.materials.show', [
            'course' => $this->week->course_id, 
            'week' => $this->week->id
        ], navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Ujian: {{ $title }}
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
                               wire:model="title" />
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                        <label class="label mt-4" for="description">Instruksi Pengerjaan</label>
                        <textarea id="description" class="textarea w-full border-black rounded-xl m-1" rows="3"
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
                            <button type="submit" class="btn px-2 bg-blue-500 px-4 by-1 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                                Simpan Perubahan
                            </button>
                            
                            <a href="{{ route('courses.materials.show', ['course' => $this->week->course_id, 'week' => $this->week->id]) }}" 
                               wire:navigate 
                               class="btn px-2 btn-ghost px-4 by-1 font-bold
                                    text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                                Batal
                            </a>
                        </div>

                    </fieldset>

                </form>
            </div>
        </div>
    </div>
</div>