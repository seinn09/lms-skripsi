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
        $enrolledClassIds = Auth::user()->enrolledClasses()
                                ->pluck('course_classes.id');

        $this->availableClasses = CourseClass::with(['course', 'pengajar'])
            ->where('status', 'open')
            ->whereNotIn('id', $enrolledClassIds)
            ->get();
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

        Auth::user()->enrolledClasses()->attach($this->classToEnroll);

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