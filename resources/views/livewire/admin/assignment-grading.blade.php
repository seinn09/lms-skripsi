<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Rule;
use App\Models\Assignment;
use App\Models\CourseClass;
use App\Models\User;
use App\Models\AssignmentSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public Assignment $assignment;
    public CourseClass $class;
    public User $student;
    
    public Collection $submissions;

    public $grade = '';

    #[Rule('nullable|string')]
    public $comment_sub = ''; 

    public function mount(Assignment $assignment, CourseClass $class, User $student): void
    {
        $this->assignment = $assignment;
        $this->class = $class;
        $this->student = $student;

        $this->submissions = $this->assignment->submissions()
                                  ->where('user_id', $student->id)
                                  ->get();

        if ($this->submissions->isNotEmpty()) {
            $firstSubmission = $this->submissions->first();
            $this->grade = $firstSubmission->grade;
            $this->comment_sub = $firstSubmission->comment_sub;
        }
    }

    public function save(): void
    {
        $this->validate([
            'grade' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (is_numeric($value)) {
                        if ($value < 0 || $value > 100) {
                            $fail('Nilai angka harus antara 0 sampai 100.');
                        }
                    } 
                    else if (!preg_match('/^[A-E][+-]?$/i', $value)) {
                        $fail('Format nilai tidak valid. Gunakan angka (0-100) atau huruf (A, B+, C, dll).');
                    }
                },
            ],
        ]);

        $this->assignment->submissions()
             ->where('user_id', $this->student->id)
             ->update([
                 'grade' => $this->grade,
                 'comment_sub' => $this->comment_sub
             ]);

        session()->flash('notify', ['type' => 'success', 'message' => 'Nilai dan komentar berhasil disimpan.']);

        $this->redirectRoute('admin.assignments.grading.class', [
            'assignment' => $this->assignment,
            'class' => $this->class
        ], navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Penilaian: {{ $student->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-6">
                <a href="{{ route('admin.assignments.grading.class', ['assignment' => $assignment, 'class' => $class]) }}" 
                   wire:navigate 
                   class="btn btn-ghost btn-sm gap-2 pl-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
                    Kembali ke Daftar Mahasiswa
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 flex items-start gap-4">
                        <div class="avatar placeholder">
                            <div class="bg-neutral text-neutral-content rounded-full w-16">
                                <span class="text-xl">{{ substr($student->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold">{{ $student->name }}</h3>
                            <p class="text-gray-500">{{ $student->email }}</p>
                            <p class="text-sm text-gray-400">NIM: {{ $student->siswa->nim ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="bg-white shadow-sm sm:rounded-lg p-6">
                        <h3 class="font-bold text-gray-800 mb-4">File Submisi</h3>
                        
                        @if ($submissions->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($submissions as $sub)
                                    <div class="flex items-center justify-between p-3 border rounded-lg bg-gray-50 hover:bg-blue-50 transition">
                                        <div class="flex items-center gap-3 overflow-hidden">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <div class="min-w-0">
                                                @php $cleanName = \Illuminate\Support\Str::after(basename($sub->file_path), '_'); @endphp
                                                <p class="font-medium text-gray-900 truncate" title="{{ $cleanName }}">{{ $cleanName }}</p>
                                                <p class="text-xs text-gray-500">
                                                    Diupload: {{ $sub->created_at->format('d M Y, H:i') }}
                                                    @if ($assignment->deadline && $sub->created_at->gt($assignment->deadline))
                                                        <span class="text-red-500 font-bold ml-1">(Terlambat)</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <a href="{{ Storage::url($sub->file_path) }}" download="{{ $cleanName }}" class="btn btn-sm btn-outline btn-primary">Download</a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 text-gray-500 italic">Mahasiswa ini belum mengumpulkan file apa pun.</div>
                        @endif
                    </div>
                </div>

                <div class="md:col-span-1">
                    <div class="bg-white shadow-sm sm:rounded-lg p-6 sticky top-6">
                        <h3 class="font-bold text-lg mb-4">Input Nilai & Feedback</h3>
                        
                        <form wire:submit="save">
                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-bold">Nilai</span>
                                </label>
                                
                                <input type="text" 
                                       wire:model="grade" 
                                       placeholder="Cth: 95 atau A" 
                                       class="input w-full border-black rounded-xl font-bold uppercase" />
                                
                                <label class="label">
                                    <span class="label-text-alt text-gray-500">Masukkan angka (0-100) atau huruf (A-E)</span>
                                </label>

                                @error('grade') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="form-control w-full mb-4">
                                <label class="label">
                                    <span class="label-text font-bold">Komentar / Feedback</span>
                                </label>
                                <textarea wire:model="comment_sub" 
                                          class="textarea w-full border-black rounded-xl m-1" 
                                          placeholder="Tulis masukan untuk mahasiswa..."></textarea>
                                @error('comment_sub') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="btn bg-blue-500 font-bold rounded-lg w-full
                                            text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-105 hover:bg-blue-600">
                                    Simpan Penilaian
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 pt-6 border-t text-sm text-gray-500 space-y-2">
                            <div class="flex justify-between">
                                <span>Status:</span>
                                @if ($grade !== null)
                                    <span class="text-green-600 font-bold">Sudah Dinilai</span>
                                @else
                                    <span class="text-gray-400">Belum Dinilai</span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</div>