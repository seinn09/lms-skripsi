<?php

use App\Models\Permission;
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

Volt::route('dashboard', 'dashboard.index')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

#ROUTE COURSE MATERIALS
Volt::route('courses/{course}/week', 'course-materials')
    ->middleware(['permission:weeks-read', 'auth', 'verified'])
    ->name('courses.materials.index');

Volt::route('courses/{course}/week/{week}', 'week-details')
    ->middleware(['permission:weeks-read', 'auth', 'verified'])
    ->name('courses.materials.show');

Volt::route('courses/week/{week}/edit', 'week-edit')
    ->middleware(['auth', 'verified', 'permission:weeks-update'])
    ->name('courses.materials.edit');

Volt::route('courses/week/{week}/materials/create', 'material-create')
    ->middleware(['auth', 'verified', 'permission:materials-create'])
    ->name('materials.create');

Volt::route('materials/{material}/edit', 'material-edit')
    ->middleware(['auth', 'verified', 'permission:materials-update'])
    ->name('materials.edit');
    
Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {

    #ROUTE MANAGE COURSES
    Volt::route('courses', 'admin.courses')
        ->middleware(['role:superadministrator|admin'])
        ->name('courses.index');

    Volt::route('courses/create', 'admin.coursecreate') 
        ->middleware(['permission:courses-create']) 
        ->name('courses.create');

    Volt::route('courses/{course}/edit', 'admin.courseedit')
        ->middleware(['permission:courses-update'])
        ->name('courses.edit');

    #ROUTE MANAGE PENGGUNA
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
