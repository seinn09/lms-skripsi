<?php

use Livewire\Volt\Component;
use App\Models\Course;
use App\Models\EnrollableCourse;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $courses;

    public function mount(): void
    {
        $this->loadCourses();
    }

    public function loadCourses(): void
    {
        $this->courses = Course::with(['enrollable', 'pengajar'])
                            ->orderBy('name', 'asc')
                            ->get();
    }

    public function openEnrollment(int $courseId): void
    {
        EnrollableCourse::create([
            'course_id' => $courseId,
        ]);

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Pendaftaran mata kuliah berhasil dibuka!'
        ]);

        $this->loadCourses();
    }

    public function closeEnrollment(int $courseId): void
    {
        EnrollableCourse::where('course_id', $courseId)->delete();

        session()->flash('notify', [
            'type' => 'warning',
            'message' => 'Pendaftaran mata kuliah telah ditutup.'
        ]);

        $this->loadCourses();
    }

}; ?>

<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kelola Pendaftaran Mata Kuliah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <h1 class="text-xl font-bold mb-4">Daftar Semua Mata Kuliah</h1>

                    <div class="overflow-x-auto rounded-box border border-base-content/5 bg-base-100 p-4">
                        <table class="table">
                            <thead>
                                <tr class="border bg-base-200 rounded-xl">
                                    <th>Nama Mata Kuliah</th>
                                    <th>Dosen Pengampu</th>
                                    <th>Status Pendaftaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($courses as $course)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $course->name }}</td>
                                        <td>{{ $course->pengajar->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($course->enrollable)
                                                <span class="badge badge-success">Dibuka</span>
                                            @else
                                                <span class="badge badge-error">Ditutup</span>
                                            @endif
                                        </td>
                                        
                                        <td class="flex gap-2">
                                            @if ($course->enrollable)
                                                 <button type="submit" wire:click="closeEnrollment({{ $course->id }})" class="btn bg-red-400 px-4 font-bold
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-500">
                                                    Tutup Pendaftaran
                                                </button>
                                            @else
                                                <button type="submit" wire:click="openEnrollment({{ $course->id }})" class="btn bg-blue-500 px-4 font-bold
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-indigo-500">
                                                    Buka Pendaftaran
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center font-bold">Belum ada data mata kuliah.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>