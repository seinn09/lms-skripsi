<?php

use Livewire\Volt\Component;
use App\Models\EnrollableCourse;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

new class extends Component
{
    public Collection $availableCourses;
    public ?int $courseToEnroll = null;

    public function mount(): void
    {
        $this->loadCourses();
    }

    public function loadCourses(): void
    {
        $enrolledCourseIds = Auth::user()->coursesAsSiswa()
                                ->pluck('courses.id');

        $this->availableCourses = EnrollableCourse::with(['course', 'course.pengajar'])
            ->where('status', 'open')
            ->whereNotIn('course_id', $enrolledCourseIds)
            ->get();
    }

    public function enroll(int $courseId): void
    {
        $this->courseToEnroll = $courseId;

        $this->js("
            Swal.fire({
                title: 'Ambil Mata Kuliah Ini?',
                text: 'Anda akan mendaftar di mata kuliah ini.',
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
        if ($this->courseToEnroll === null) {
            return;
        }

        Auth::user()->coursesAsSiswa()->attach($this->courseToEnroll);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Anda berhasil mendaftar di mata kuliah ini!'
        ]);

        $this->loadCourses();

        $this->courseToEnroll = null;
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

                @forelse ($availableCourses as $enrollable)
                    <div class="card bg-primary text-primary-content shadow-xl w-full 
                                    group transition-all duration-500">
                        <div class="card-body">
                            <h2 class="card-title font-bold text-xl">{{ $enrollable->course->name }}</h2>
                            <p class="text-sm text-white mb-2">
                                Dosen Pengampu: {{ $enrollable->course->pengajar->name ?? 'N/A' }}
                            </p>
                           
                            <p class="line-clamp-2 group-hover:line-clamp-none">
                                    {{ $enrollable->course->description }}
                            </p>
                            
                            <div class="card-actions justify-end">
                                <button class="btn px-2 py-2 text-black bg-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300"
                                        wire:click="enroll({{ $enrollable->course_id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="enroll({{ $enrollable->course_id }})">
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