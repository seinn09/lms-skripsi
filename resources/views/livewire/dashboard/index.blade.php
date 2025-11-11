<?php
use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use function Livewire\Volt\layout;

new class extends Component
{
    public Collection $courses;

    public function mount(): void
    {
        if (auth()->check()) {
            $user = auth()->user(); 
            
            if ($user->hasRole(['superadministrator', 'admin'])) {
                $this->courses = Course::with('pengajar')->orderBy('name', 'asc')->get();
            } 
            elseif ($user->hasRole('pengajar')) {
                $this->courses = $user->coursesAsPengajar()->with('pengajar')->orderBy('name', 'asc')->get();
            } 
            elseif ($user->hasRole('siswa')) {
                $this->courses = $user->coursesAsSiswa()->with('pengajar')->orderBy('name', 'asc')->get();
            } 
            else {
                $this->courses = collect();
            }

        } else {
            $this->courses = new \Illuminate\Database\Eloquent\Collection();
            layout('components.layouts.guest'); 
        }
    }
}; ?>

<div>
    <div class="hero min-h-screen" style="background-image: url({{ asset('images/background-hero.jpeg') }});">
        <div class="hero-overlay bg-opacity-60"></div>
        <div class="hero-content text-neutral-content text-center">
            <div class="max-w-md">
                <h1 class="mb-5 text-5xl font-bold">Selamat Datang di LMS</h1>
                <p class="mb-5">
                    Lorem ipsum, dolor sit amet consectetur adipisicing elit. Quisquam quas cum velit. Labore voluptates, tenetur odio ipsa eum reiciendis cupiditate dolorem nam tempora facilis culpa porro minus atque eaque illum.
                </p> 
                @auth
                    <a href="#course-section" class="btn btn-primary">
                        Lihat Mata Kuliah!
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login untuk Memulai</a>
                @endauth
            </div>
        </div>
    </div>

    @auth
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard Mata Kuliah') }}
            </h2>
        </x-slot>

        <div class="py-12" id="course-section">
            <div class="flex w-full flex-col py-10">
                <div class="card bg-gray-300 rounded-box grid h-20 place-items-center font-bold text-2xl">Mata Kuliah Anda</div>
            </div>
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

    @else
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Dashboard') }}
            </h2>
        </x-slot>
    @endauth
</div>