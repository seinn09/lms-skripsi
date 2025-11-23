<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Week;
use App\Models\Assignment;

new class extends Component
{
    public Assignment $assignment;
    public Week $week;

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    #[Rule('nullable|date')]
    public ?string $deadline = null;

    public function mount(Assignment $assignment): void
    {
        $this->assignment = $assignment;
        
        $this->week = $assignment->week;

        $this->title = $assignment->title;
        $this->description = $assignment->description;
        
        $this->deadline = $assignment->deadline ? $assignment->deadline->format('Y-m-d\TH:i') : null;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->assignment->update($validated);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Tugas berhasil diperbarui!'
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
            {{ __('Edit Tugas: ') }} {{ $title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Tugas</legend>

                            <label class="label" for="title">Judul Tugas</label>
                            <input id="title" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Contoh: Esai Bab 1" 
                                   wire:model="title" />
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="description">Deskripsi / Instruksi</label>
                            <textarea id="description" class="textarea w-full border-black rounded-xl m-1" rows="5"
                                      placeholder="Jelaskan instruksi tugas di sini..."
                                      wire:model="description"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                            <label class="label mt-4" for="deadline">Batas Waktu (Deadline)</label>
                            <input id="deadline" type="datetime-local" class="input w-full border-black rounded-xl" 
                                   wire:model="deadline" />
                            @error('deadline') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <div class="mt-6 flex gap-3">
                                <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                    Simpan
                                </button>
                                <a href="{{ route('courses.materials.show', ['course' => $this->week->course_id, 'week' => $this->week->id]) }}" wire:navigate 
                                    class="btn btn-ghost bg-gray-200 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300">
                                    Batal
                                </a>
                            </div>

                        </fieldset>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>