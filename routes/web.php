<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


    
Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {

    Volt::route('courses', 'admin.courses')
        ->middleware(['role:superadministrator|admin|pengajar'])
        ->name('courses.index');

    Volt::route('courses/create', 'admin.coursecreate') 
        ->middleware(['permission:courses-create']) 
        ->name('courses.create');

    Volt::route('pengguna', 'admin.pengguna')
        ->middleware(['permission:users-read'])
        ->name('pengguna.index');

    Volt::route('pengguna/create', 'admin.penggunacreate')
        ->middleware(['permission:users-create'])
        ->name('pengguna.create');

    Volt::route('pengguna/{user}/edit', 'admin.penggunaedit')
        ->middleware(['permission:users-update'])
        ->name('pengguna.edit');
});

require __DIR__.'/auth.php';
