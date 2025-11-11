<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Week;
use App\Models\Material;
use Livewire\WithFileUploads;

new class extends Component
{
    use WithFileUploads;

    public Week $week;

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240')]
    public $file;

    #[Rule('nullable|url|required_without:file')]
    public string $external_link = '';

    public function mount(Week $week): void
    {
        $this->week = $week;
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|min:3',
            'external_link' => 'nullable|url|required_without:file',
            'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240|required_without:external_link',
        ]);

        $filePath = null;

        if ($this->file) {
            $filePath = $this->file->store('materials', 'public');
        }

        Material::create([
            'week_id' => $this->week->id,
            'title' => $this->title,
            'file_path' => $filePath,
            'external_link' => $this->external_link,
        ]);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Materi baru berhasil ditambahkan!'
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
            Tambah Materi ke: {{ $week->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Materi</legend>
                            <p class="text-sm text-gray-600 mb-4">Isi salah satu: File Upload (untuk PPT, PDF) ATAU Link Eksternal (untuk GDrive, YouTube).</p>

                            <label class="label" for="title">Judul Materi</label>
                            <input id="title" type="text" class="input w-full border-black rounded-xl" 
                                   placeholder="Contoh: Slide PPT Pertemuan 1" 
                                   wire:model="title" />
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="external_link">Link Eksternal (GDrive, YouTube, dll.)</label>
                            <input id="external_link" type="text" class="input w-full border-black rounded-xl" 
                                      placeholder="https://..."
                                      wire:model="external_link" />
                            @error('external_link') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="file">ATAU Upload File (PDF, PPT, ZIP)</label>
                            <input id="file" type="file" class="file-input file-input-bordered w-full border-black rounded-xl" 
                                   wire:model="file" />
                            
                            <div wire:loading wire:target="file" class="text-sm text-blue-500 mt-2">
                                Mengupload file...
                            </div>
                            
                            @error('file') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

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