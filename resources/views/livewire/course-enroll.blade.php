<?php

use Livewire\Volt\Component;
use App\Models\CourseClass;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Collection $availableClasses;
    public ?int $classToEnroll = null;

    public function mount(): void
    {
        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        $user = Auth::user();
        $enrolledClassIds = $user->enrolledClasses()
                                ->pluck('course_classes.id');

        $query = CourseClass::with(['course.studyProgram', 'pengajar'])
            ->where('status', 'open')
            ->whereNotIn('id', $enrolledClassIds);

        // Filter by student's study program and tenant
        if ($user->siswa && $user->siswa->study_program_id) {
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('study_program_id', $user->siswa->study_program_id)
                  ->where('tenant_id', $user->tenant_id);
            });
        } else {
            // If not a student, still filter by tenant
            $query->whereHas('course', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
        }

        $this->availableClasses = $query->get();
    }

    public function confirmEnroll(int $classId): void
    {
        $this->classToEnroll = $classId;

        $this->js("
            Swal.fire({
                title: 'Ambil Kelas Ini?',
                text: 'Anda akan mendaftar di kelas ini.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Ambil!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Panggil method 'doEnroll' di backend
                    \$wire.doEnroll();
                }
            })
        ");
    }

    public function doEnroll(): void
    {
        if ($this->classToEnroll === null) {
            return;
        }

        $user = Auth::user();
        $courseClass = CourseClass::with('course')->find($this->classToEnroll);

        if (!$courseClass) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Kelas tidak ditemukan!'
            ]);
            $this->classToEnroll = null;
            return;
        }

        // Validate tenant
        if ($courseClass->course->tenant_id !== $user->tenant_id) {
            session()->flash('notify', [
                'type' => 'error',
                'message' => 'Anda tidak dapat mendaftar di mata kuliah dari kampus lain!'
            ]);
            $this->classToEnroll = null;
            return;
        }

        // Validate that the course belongs to the student's study program
        if ($user->siswa && $courseClass) {
            if ($courseClass->course->study_program_id !== $user->siswa->study_program_id) {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Anda tidak dapat mendaftar di mata kuliah dari program studi lain!'
                ]);
                $this->classToEnroll = null;
                return;
            }
        }

        $user->enrolledClasses()->attach($this->classToEnroll);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Anda berhasil mendaftar di kelas ini!'
        ]);

        $this->loadClasses();

        $this->classToEnroll = null;
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Ambil Mata Kuliah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-8">
                <h1 class="text-2xl font-bold">Mata Kuliah yang Ditawarkan</h1>
                <p class="text-sm text-gray-600">Berikut adalah daftar mata kuliah yang pendaftarannya sedang dibuka dan belum Anda ambil.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                @forelse ($availableClasses as $class)
                    <div class="card bg-primary text-primary-content shadow-xl w-full
                                    group transition-all duration-500">
                        <div class="card-body">
                            <h2 class="card-title font-bold text-xl">{{ $class->course->name}}</h2>
                            <span class="badge badge-ghost badge-sm mb-2 w-fit">{{ $class->class_code }}</span>

                            <p class="text-sm text-white mb-2">
                                Dosen Pengampu: {{ $class->pengajar->name ?? 'N/A' }}
                            </p>

                            <p class="line-clamp-2 group-hover:line-clamp-none">
                                    {{ $class->course->description }}
                            </p>

                            <div class="card-actions justify-end">
                                <button class="btn px-2 py-2 text-black bg-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300"
                                        wire:click="confirmEnroll({{ $class->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="confirmEnroll({{ $class->id }})">
                                    Ambil Mata Kuliah
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-6 bg-white shadow-sm sm:rounded-lg text-center">
                        <p class="font-bold">Tidak ada mata kuliah yang tersedia untuk Anda saat ini.</p>
                        <p class="text-sm text-gray-600">(Semua mata kuliah yang dibuka mungkin sudah Anda ambil).</p>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</div>
