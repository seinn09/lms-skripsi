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

                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-xl font-bold">Daftar Mata Kuliah</h1>

                        @permission('courses-create')
                            <a href="{{ route('admin.courses.create') }}" wire:navigate 
                               class="btn btn-primary btn-sm text-white">
                                + Tambah Course Baru
                            </a>
                        @endpermission
                    </div>
                    
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