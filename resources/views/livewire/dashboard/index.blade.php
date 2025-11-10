<?php
use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $courses;

    public function mount(): void
    {
        $user = auth()->user();

        if ($user->hasRole(['superadministrator', 'admin'])) {
            $this->courses = Course::with('pengajar')
                                ->orderBy('name', 'asc')
                                ->get();
        } 
        elseif ($user->hasRole('pengajar')) {
            $this->courses = $user->coursesAsPengajar()
                                ->with('pengajar')
                                ->orderBy('name', 'asc')
                                ->get();
        } 
        elseif ($user->hasRole('siswa')) {
            $this->courses = $user->coursesAsSiswa()
                                ->with('pengajar')
                                ->orderBy('name', 'asc')
                                ->get();
        } 
        else {
            $this->courses = collect();
        }
    }
}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($courses as $course)
                    <div class="card bg-primary text-primary-content shadow-xl w-full 
                                group transition-all duration-500">
                        <div class="card-body">
                            <h1 class="card-title font-bold text-xl">{{ $course->name }}</h1>

                            @role('superadministrator|admin')
                                <p class="text-sm text-white mb-2">Dosen Pengampu: {{ $course->pengajar->name ?? 'N/A' }}</p>
                            @endrole

                            <p class="line-clamp-2 group-hover:line-clamp-none">
                                {{ $course->description }}
                            </p>
                            
                            <div class="card-actions justify-end">
                                <a href="{{ route('courses.materials.index', $course) }}" wire:navigate 
                                   class="btn btn-sm text-black bg-white
                                   transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-gray-300">
                                    Lihat Materi
                                </a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-6 bg-white shadow-sm sm:rounded-lg text-center">
                        <p>Anda belum terdaftar di mata kuliah manapun.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>