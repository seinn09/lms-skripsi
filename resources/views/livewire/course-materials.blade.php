<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\Week;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Course $course;
    public Collection $weeks;

    public function mount(Course $course): void
    {
        $this->course = $course;
        $user = auth()->user();

        // Authorization check for students
        if ($user->hasRole('siswa')) {
            // Check if student is enrolled in any class of this course
            $isEnrolled = $user->enrolledClasses()
                ->whereHas('course', function ($query) use ($course) {
                    $query->where('id', $course->id);
                })
                ->exists();

            if (!$isEnrolled) {
                session()->flash('notify', [
                    'type' => 'error',
                    'message' => 'Anda tidak memiliki akses ke mata kuliah ini. Silakan daftar terlebih dahulu.'
                ]);
                $this->redirectRoute('dashboard', navigate: true);
                return;
            }
        }

        $this->weeks = $course->weeks()
                            ->orderBy('week_number', 'asc')
                            ->get();
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $course->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h1 class="text-2xl font-bold mb-6">Daftar Pertemuan</h1>

                    <ul class="space-y-4">
                        
                        @forelse ($weeks as $week)
                            <li class="block p-6 border rounded-lg shadow-sm hover:bg-gray-50 transition">
                                <div class="flex justify-between items-center">
                                    
                                    <a href="{{ route('courses.materials.show', ['course' => $course, 'week' => $week]) }}" wire:navigate class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 hover:text-primary">
                                            {{ $week->title }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">
                                            {{ \Illuminate\Support\Str::limit($week->description, 100) }}
                                        </p>
                                    </a>

                                    @permission('weeks-update')
                                        <a href="{{ route('courses.materials.edit', ['week' => $week]) }}" wire:navigate
                                           class="btn btn-ghost btn-sm btn-circle ml-4">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                            </svg>
                                        </a>
                                    @endpermission
                                </div>
                            </li>
                        @empty
                            <li class="p-6 border rounded-lg text-center">
                                <p class="font-bold">Belum ada pertemuan (weeks) yang dibuat untuk mata kuliah ini.</p>
                                <p class="text-sm text-gray-600">(Jalankan 'php artisan migrate:fresh --seed' untuk mengisi data dummy)</p>
                            </li>
                        @endforelse

                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>