<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    use WithFileUploads;

    public Assignment $assignment;
    public Collection $existingSubmissions;

    #[Rule('nullable|file|mimes:pdf,doc,docx,zip,rar,png,jpg|max:10240')]
    public $newFile;

    public function mount(Assignment $assignment): void
    {
        $this->assignment = $assignment;
        $this->loadSubmissions();
    }

    public function loadSubmissions(): void
    {
        $this->existingSubmissions = $this->assignment->submissions()
                                          ->where('user_id', Auth::id())
                                          ->get();
    }

    public function deleteFile($submissionId): void
    {
        $submission = AssignmentSubmission::find($submissionId);

        if ($submission && $submission->user_id === Auth::id()) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
            $submission->delete();
            
            $this->loadSubmissions();
        }
    }

    public function save(): void
    {
        $this->validate();

        if ($this->newFile) {
            $originalName = $this->newFile->getClientOriginalName();
            $fileName = time() . '_' . $originalName; 
            
            $path = $this->newFile->storeAs('submissions', $fileName, 'public');

            AssignmentSubmission::create([
                'assignment_id' => $this->assignment->id,
                'user_id' => Auth::id(),
                'file_path' => $path,
                'submitted_at' => now(),
            ]);
        }

        session()->flash('notify', ['type' => 'success', 'message' => 'Perubahan berhasil disimpan.']);
        
        $this->redirectRoute('assignments.show', $this->assignment, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit submission') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <h1 class="text-2xl font-bold mb-6">{{ $assignment->title }}</h1>

                <form wire:submit="save">
                    
                    <div class="mb-6">
                        <label class="label font-bold">File submissions</label>
                        
                        @if ($existingSubmissions->isNotEmpty())
                            <div class="border rounded-lg p-4 bg-gray-50 space-y-3">
                                @foreach ($existingSubmissions as $sub)
                                    <div class="flex items-center justify-between bg-white p-3 border rounded shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            
                                            @php
                                                $fullName = basename($sub->file_path);
                                                $cleanName = \Illuminate\Support\Str::after($fullName, '_');
                                            @endphp

                                            <a href="{{ Storage::url($sub->file_path) }}" download="{{ $cleanName }}" class="text-blue-600 hover:underline font-medium">
                                                {{ $cleanName }}
                                            </a>
                                        </div>

                                        <button type="button" 
                                                wire:click="deleteFile({{ $sub->id }})"
                                                class="btn btn-ghost btn-xs text-red-500 hover:bg-red-50"
                                                title="Hapus file ini">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">Belum ada file yang diupload.</p>
                        @endif
                    </div>

                    <div class="mb-8">
                        <label class="label font-bold">Upload File Baru</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-blue-500 transition bg-gray-50 flex flex-col items-center justify-center min-h-[150px]">
                            
                            <input type="file" wire:model="newFile" id="file-upload" class="hidden" />
                            
                            @if ($newFile)
                                <div class="flex flex-col items-center animate-pulse">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    
                                    <p class="text-sm text-gray-900 font-bold">
                                        {{ $newFile->getClientOriginalName() }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">Siap untuk disimpan</p>

                                    <button type="button" wire:click="$set('newFile', null)" 
                                            class="btn btn-xs btn-ghost text-red-500 mt-3">
                                        Ganti / Batalkan
                                    </button>
                                </div>

                            @else
                                <label for="file-upload" class="cursor-pointer flex flex-col items-center gap-2 w-full h-full justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                    </svg>
                                    <span class="text-gray-600 font-medium">Klik untuk memilih file</span>
                                    <span class="text-xs text-gray-400">(Maksimal 10MB)</span>
                                </label>
                            @endif

                            <div wire:loading wire:target="newFile" class="mt-2 text-sm text-blue-500">
                                Sedang memproses file...
                            </div>
                            
                            @error('newFile') <span class="text-red-500 text-sm block mt-2">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex justify-center gap-4">
                        <button type="submit" class="btn px-3 bg-blue-500 font-bold rounded-lg 
                            text-black transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                            Simpan Perubahan
                        </button>
                        <a href="{{ route('assignments.show', $assignment) }}" wire:navigate 
                           class="btn btn-ghost transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300">
                            Batal
                        </a>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>