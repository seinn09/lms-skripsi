<?php

use Livewire\Volt\Component;
use App\Models\CourseClass;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $enrolledClasses;
    public float $ipk = 0.0;
    public int $totalSks = 0;

    public function mount(): void
    {
        $this->loadTranskrip();
    }

    public function loadTranskrip(): void
    {
        $user = Auth::user();

        // 1. Ambil semua kelas yang diambil siswa
        $this->enrolledClasses = $user->enrolledClasses()
                                      ->with('course')
                                      ->get();

        // 2. Hitung Total SKS Diambil (Semua matkul yang di-enroll)
        // Tidak peduli nilainya sudah keluar atau belum
        $this->totalSks = $this->enrolledClasses->sum(fn($class) => $class->course->sks);

        // 3. Hitung IPK (Hanya dari matkul yang SUDAH dinilai)
        $totalMutu = 0;
        $totalSksGraded = 0; // Variabel sementara untuk pembagi IPK

        foreach ($this->enrolledClasses as $class) {
            // Cek apakah nilai sudah difinalisasi dosen
            if ($class->pivot->grade_point !== null) {
                $sks = $class->course->sks;
                $bobot = $class->pivot->grade_point;

                $totalMutu += ($sks * $bobot);
                $totalSksGraded += $sks; // Tambahkan SKS ke pembagi IPK
            }
        }

        // Hindari pembagian dengan nol
        if ($totalSksGraded > 0) {
            $this->ipk = $totalMutu / $totalSksGraded;
        } else {
            $this->ipk = 0.0;
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Transkrip Nilai Akademik') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="mb-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-500">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Indeks Prestasi Kumulatif (IPK)</h3>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-4xl font-extrabold text-gray-900">
                            {{ number_format($ipk, 2) }}
                        </span>
                        <span class="text-sm text-gray-500">/ 4.00</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        *Dihitung dari mata kuliah yang sudah dinilai saja
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-500">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Total SKS Diambil</h3>
                    <div class="mt-2">
                        <span class="text-4xl font-extrabold text-gray-900">
                            {{ $totalSks }}
                        </span>
                        <span class="text-sm text-gray-500">SKS</span>
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        (Termasuk mata kuliah yang sedang berjalan)
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-purple-500">
                    <h3 class="text-gray-500 text-sm font-bold uppercase tracking-wider">Jumlah Mata Kuliah</h3>
                    <div class="mt-2">
                        <span class="text-4xl font-extrabold text-gray-900">
                            {{ $enrolledClasses->count() }}
                        </span>
                        <span class="text-sm text-gray-500">Matkul</span>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Rincian Hasil Studi</h3>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="bg-base-200 border-b">
                                    <th>Kode MK</th>
                                    <th>Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Semester</th>
                                    <th class="text-center">Nilai Angka</th>
                                    <th class="text-center">Nilai Huruf</th>
                                    <th class="text-center">Bobot (Indeks)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($enrolledClasses as $class)
                                    <tr class="hover:bg-base-50">
                                        <td class="font-mono text-xs">{{ $class->course->course_code }}</td>
                                        <td>
                                            <div class="font-bold">{{ $class->course->name }}</div>
                                            <div class="text-xs text-gray-500">Kelas: {{ $class->class_code }}</div>
                                        </td>
                                        <td>{{ $class->course->sks }}</td>
                                        <td>{{ $class->semester }}</td>
                                        
                                        <td class="text-center">
                                            @if ($class->pivot->final_score !== null)
                                                {{ $class->pivot->final_score }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center font-bold">
                                            @if ($class->pivot->final_grade)
                                                <span class="{{ in_array($class->pivot->final_grade, ['A', 'A-']) ? 'text-green-600' : 'text-gray-800' }}">
                                                    {{ $class->pivot->final_grade }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>

                                        <td class="text-center font-mono">
                                            @if ($class->pivot->grade_point !== null)
                                                {{ number_format($class->pivot->grade_point, 2) }}
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-8 text-gray-500">
                                            Belum ada data hasil studi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 text-xs text-gray-500 italic">
                        * Nilai yang ditampilkan adalah nilai final yang telah divalidasi oleh dosen.
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>