<?php

use Livewire\Volt\Component;
use App\Models\CourseClass;
use Illuminate\Database\Eloquent\Collection;

new class extends Component
{
    public Collection $classes;

    public function mount(): void
    {
        $this->loadClasses();
    }

    public function loadClasses(): void
    {
        $this->classes = CourseClass::with(['course', 'pengajar'])
                            ->orderBy('created_at', 'asc')
                            ->get();
    }

    public function openEnrollment(int $classId): void
    {
        $class = CourseClass::find($classId);
        if ($class) {
            $class->update(['status' => 'open']);
        }

        session()->flash('notify', [
            'type' => 'success',
            'message' => 'Pendaftaran kelas berhasil dibuka!'
        ]);

        $this->loadClasses();
    }

    public function closeEnrollment(int $classId): void
    {
        $class = CourseClass::find($classId);
        if ($class) {
            $class->update(['status' => 'closed']);
        }

        session()->flash('notify', [
            'type' => 'warning',
            'message' => 'Pendaftaran kelas telah ditutup.'
        ]);

        $this->loadClasses();
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
                                    <th>Mata Kuliah</th>
                                    <th>Kode Kelas</th>
                                    <th>Dosen Pengampu</th>
                                    <th>Semester</th>
                                    <th>Kapasitas</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($classes as $class)
                                    <tr class="hover:bg-base-300">
                                        <td>{{ $class->course->name ?? 'N/A' }}</td>
                                        <td><span class="badge badge-ghost">{{ $class->class_code }}</span></td>
                                        <td>{{ $class->pengajar->name ?? 'N/A' }}</td>
                                        <td>{{ $class->semester }}</td>
                                        <td>{{ $class->capacity }}</td>
                                        <td>
                                            @if ($class->status == 'open')
                                                <span class="badge badge-success">Dibuka</span>
                                            @else
                                                <span class="badge badge-error">Ditutup</span>
                                            @endif
                                        </td>
                                        
                                        <td class="flex gap-2">
                                            @if ($class->status == 'open')
                                                 <button type="submit" wire:click="closeEnrollment({{ $class->id }})" class="py-2 px-4 bg-red-400 font-bold rounded-md
                                                    text-white transition delay-150 duration-300 ease-in-out hover:-translate-y-1 hover:scale-110 hover:bg-red-500">
                                                    Tutup Pendaftaran
                                                </button>
                                            @else
                                                <button type="submit" wire:click="openEnrollment({{ $class->id }})" class="py-2 px-4 bg-blue-500 font-bold rounded-md
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