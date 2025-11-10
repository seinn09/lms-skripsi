<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Week;

new class extends Component
{
    public Week $week;

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|string')]
    public string $description = '';

    public function mount(Week $week): void
    {
        $this->week = $week;

        $this->title = $week->title;
        $this->description = $week->description;
    }

    public function save(): void
    {
        $validated = $this->validate();

        $this->week->update($validated);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Detail pertemuan berhasil diperbarui!'
        ]);

        $this->redirectRoute('courses.materials.index', ['course' => $this->week->course_id], navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pertemuan: ') }} {{ $week->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Pertemuan (Minggu ke-{{ $week->week_number }})</legend>

                            <label class="label" for="title">Judul Pertemuan</label>
                            <input id="title" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Contoh: Pendahuluan" 
                                   wire:model="title" />
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="description">Deskripsi/Konten</label>
                            <textarea id="description" class="textarea w-full border-black rounded-xl" rows="4"
                                      placeholder="Deskripsi singkat, link GMeet, dll..."
                                      wire:model="description"></textarea>
                            @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <div class="mt-6 flex gap-3">
                                <button type="submit" class="btn bg-blue-500 px-4 font-bold
                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                    Simpan
                                </button>
                                <a href="{{ route('courses.materials.index', ['course' => $this->week->course_id]) }}" wire:navigate 
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