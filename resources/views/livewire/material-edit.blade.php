<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Material;
use App\Models\Week;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    use WithFileUploads;

    public Material $material;
    public Week $week;

    #[Rule('required|string|min:3')]
    public string $title = '';

    #[Rule('nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240')]
    public $file;

    #[Rule('nullable|url|required_without:file')]
    public string $external_link = '';


    public function mount(Material $material): void
    {
        $this->material = $material;
        $this->week = $material->week;

        $this->title = $material->title;
        $this->external_link = $material->external_link ?? '';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'title' => 'required|string|min:3',
            'external_link' => 'nullable|url',
        ]);
        
        $filePath = $this->material->file_path;

        if ($this->file) {
            $this->validate([
                'file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,zip,rar|max:10240',
            ]);

            if ($this->material->file_path) {
                Storage::disk('public')->delete($this->material->file_path);
            }

            $filePath = $this->file->store('materials', 'public');
            
            $validated['external_link'] = ''; 
        } 
        elseif ($this->external_link) {
            if ($this->material->file_path) {
                Storage::disk('public')->delete($this->material->file_path);
            }
            $filePath = null;
        }

        $this->material->update([
            'title' => $validated['title'],
            'file_path' => $filePath,
            'external_link' => $validated['external_link'],
        ]);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Materi berhasil diperbarui!'
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
            Edit Materi: {{ $material->title }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <form wire:submit="save">
                        
                        <fieldset class="fieldset bg-base-100 border-base-300 rounded-box w-full border p-4">
                            <legend class="fieldset-legend text-lg font-semibold">Detail Materi</legend>
                            <p class="text-sm text-gray-600 mb-4">Ubah judul, atau ganti file/link di bawah ini.</p>

                            <label class="label" for="title">Judul Materi</label>
                            <input id="title" type="text" class="input w-full border-black rounded-xl" 
                                   wire:model="title" />
                            @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="external_link">Link Eksternal (GDrive, YouTube, dll.)</label>
                            <input id="external_link" type="text" class="input w-full border-black rounded-xl" 
                                      placeholder="https://..."
                                      wire:model="external_link" />
                            @error('external_link') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            
                            <label class="label mt-4" for="file">ATAU Ganti File (PDF, PPT, ZIP)</label>
                            
                            @if ($material->file_path && !$file)
                                <div class="text-sm text-gray-500 mb-2">
                                    File saat ini: 
                                    <a href="{{ Storage::url($material->file_path) }}" target="_blank" class="link">
                                        {{ $material->title }}.{{ pathinfo($material->file_path, PATHINFO_EXTENSION) }}
                                    </a>
                                </div>
                            @endif
                            
                            <input id="file" type="file" class="file-input file-input-bordered w-full border-black rounded-xl" 
                                   wire:model="file" />
                            
                            <div wire:loading wire:target="file" class="text-sm text-blue-500 mt-2">
                                Mengupload file baru...
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