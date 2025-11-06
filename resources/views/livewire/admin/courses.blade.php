<?php

use Livewire\Volt\Component;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $courses;

    public function mount(): void
    {
        $this->courses = Course::all();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manajemen Courses') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h1 class="text-xl font-bold mb-4">Daftar Mata Kuliah</h1>

                    <ul>
                        @forelse ($courses as $course)
                            <li class="mb-2 p-2 border rounded">
                                {{ $course->name }}
                            </li>
                        @empty
                            <li>
                                <p>Belum ada data mata kuliah.</p>
                            </li>
                        @endforelse
                    </ul>

                </div>
            </div>
        </div>
    </div>
</div>