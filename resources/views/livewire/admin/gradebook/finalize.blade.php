<?php

use Livewire\Volt\Component;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public CourseClass $class;
    public Collection $students;

    // Array untuk menampung input nilai
    // Format: [ user_id => [ 'score' => 85, 'grade' => 'A', 'point' => 4.0 ] ]
    public array $grades = [];

    public function mount(CourseClass $courseClass): void
    {
        $this->class = $courseClass->load('course');
        
        // Ambil mahasiswa beserta data nilai akhir yang sudah ada (di pivot)
        $this->students = $this->class->students()
                                ->orderBy('name', 'asc')
                                ->get();

        // Inisialisasi array grades dari data database
        foreach ($this->students as $student) {
            $this->grades[$student->id] = [
                'score' => $student->pivot->final_score,
                'grade' => $student->pivot->final_grade,
                'point' => $student->pivot->grade_point,
            ];
        }
    }

    /**
     * Logic: Hitung Grade Otomatis saat Score berubah
     */
    public function updatedGrades($value, $key)
    {
        // $key formatnya: "1.score" (user_id.field)
        $parts = explode('.', $key);
        
        // Jika yang berubah adalah 'score', maka hitung grade & point
        if (count($parts) === 2 && $parts[1] === 'score') {
            $userId = $parts[0];
            $score = floatval($value);

            // Rumus Konversi Nilai (Standar Umum)
            // Anda bisa sesuaikan range ini dengan aturan kampus
            if ($score >= 85) { $g='A'; $p=4.0; }
            elseif ($score >= 80) { $g='A-'; $p=3.7; }
            elseif ($score >= 75) { $g='B+'; $p=3.3; }
            elseif ($score >= 70) { $g='B'; $p=3.0; }
            elseif ($score >= 65) { $g='B-'; $p=2.7; }
            elseif ($score >= 60) { $g='C+'; $p=2.3; }
            elseif ($score >= 55) { $g='C'; $p=2.0; }
            elseif ($score >= 40) { $g='D'; $p=1.0; }
            else { $g='E'; $p=0.0; }

            // Update array
            $this->grades[$userId]['grade'] = $g;
            $this->grades[$userId]['point'] = $p;
        }
    }

    public function save(): void
    {
        DB::transaction(function () {
            foreach ($this->grades as $userId => $data) {
                // Update tabel pivot 'course_student'
                $this->class->students()->updateExistingPivot($userId, [
                    'final_score' => $data['score'] !== '' ? $data['score'] : null,
                    'final_grade' => $data['grade'] ?? null,
                    'grade_point' => $data['point'] ?? null,
                ]);
            }
        });

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Nilai akhir berhasil disimpan!'
        ]);

        // Kembali ke Gradebook Matrix
        $this->redirectRoute('courses.gradebook', $this->class->course_id, navigate: true);
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Finalisasi Nilai: {{ $class->course->name }} ({{ $class->class_code }})
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                <div class="mb-6 border-b pb-4">
                    <h1 class="text-xl font-bold">Input Nilai Akhir</h1>
                    <p class="text-sm text-gray-500">
                        Masukkan <strong>Nilai Angka (0-100)</strong>. Sistem akan otomatis mengonversi ke Huruf dan Bobot.
                    </p>
                </div>

                <form wire:submit="save">
                    <div class="overflow-x-auto border rounded-lg mb-6">
                        <table class="table table-zebra">
                            <thead>
                                <tr class="bg-base-200">
                                    <th class="w-10">No</th>
                                    <th>Mahasiswa</th>
                                    <th>NIM</th>
                                    <th class="w-32 text-center">Nilai Angka (0-100)</th>
                                    <th class="w-24 text-center">Grade (Huruf)</th>
                                    <th class="w-24 text-center">Bobot (Point)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($students as $index => $student)
                                    <tr class="hover">
                                        <th>{{ $index + 1 }}</th>
                                        <td>
                                            <div class="font-bold">{{ $student->name }}</div>
                                            <div class="text-xs text-gray-400">{{ $student->email }}</div>
                                        </td>
                                        <td class="font-mono">{{ $student->siswa->nim ?? '-' }}</td>
                                        
                                        <td>
                                            <input type="number" 
                                                   wire:model.live.debounce.500ms="grades.{{ $student->id }}.score"
                                                   class="input input-sm w-full border-black rounded-xl text-center font-bold"
                                                   min="0" max="100" step="0.01" placeholder="0">
                                        </td>

                                        <td>
                                            <input type="text" 
                                                   wire:model="grades.{{ $student->id }}.grade"
                                                   class="input input-sm w-full border-black rounded-xl text-center bg-gray-100 font-bold text-blue-600"
                                                   readonly tabindex="-1">
                                        </td>

                                        <td>
                                            <input type="number" 
                                                   wire:model="grades.{{ $student->id }}.point"
                                                   class="input input-sm w-full border-black rounded-xl text-center bg-gray-100"
                                                   readonly tabindex="-1">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-8 text-gray-500">
                                            Tidak ada mahasiswa di kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('courses.gradebook', $class->course_id) }}" wire:navigate 
                           class="py-2 px-4 text-base rounded-md text-black font-bold btn-ghost
                                 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-200">
                            Batal
                        </a>
                        <button type="submit" class="btn py-2 px-4 text-base rounded-md text-white bg-blue-500 font-bold
                                 transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-blue-600">
                            Simpan Finalisasi
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>