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

Route::get('/', fn () => redirect()->route('dashboard'));

Volt::route('dashboard', 'dashboard.index')
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Volt::route('courses', 'admin.courses')
    ->middleware(['role:superadministrator|admin|pengajar'])
    ->name('courses.index');

#ROUTE COURSE DETAIL
Volt::route('courses/{course}/detail', 'admin.course-detail')
    ->middleware(['role:superadministrator|admin|pengajar'])
    ->name('courses.detail');

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


#ROUTE COURSE ASSIGNMENTS
Volt::route('courses/week/{week}/assignments/create', 'assignment-create')
    ->middleware(['auth', 'verified', 'permission:assignments-create'])
    ->name('assignments.create');

Volt::route('assignments/{assignment}/edit', 'assignment-edit')
    ->middleware(['auth', 'verified', 'permission:assignments-update'])
    ->name('assignments.edit');

Volt::route('assignments/{assignment}', 'assignment-show')
    ->middleware(['auth', 'verified'])
    ->name('assignments.show');

#ROUTE SUBMISSIONS
Volt::route('assignments/{assignment}/submission', 'submission-edit')
    ->middleware(['auth', 'verified'])
    ->name('assignments.submission');

#ROUTE COURSE ENROLLMENT FOR STUDENTS
Volt::route('enroll-courses', 'course-enroll')
    ->middleware(['auth', 'verified', 'role:siswa|superadministrator|admin'])
    ->name('courses.enroll.index');
    
Route::prefix('admin')->middleware(['auth', 'verified'])->name('admin.')->group(function () {

    #ROUTE MANAGE COURSEs
    Volt::route('courses/create', 'admin.coursecreate') 
        ->middleware(['role:superadministrator|admin']) 
        ->name('courses.create');

    Volt::route('courses/{course}/edit', 'admin.courseedit')
        ->middleware(['role:superadministrator|admin'])
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

    #ROUTE UNTUK ASSIGNMENT SUBMISSIONS MANAGEMENT
    Volt::route('assignments/{assignment}/class/{class}/grading', 'admin.assignment-class-grades')
        ->middleware(['auth', 'verified', 'permission:submissions-update'])
        ->name('assignments.grading.class');

    Volt::route('assignments/{assignment}/class/{class}/grading/{student}', 'admin.assignment-grading')
        ->middleware(['auth', 'verified', 'permission:submissions-update'])
        ->name('assignments.grading.student');

    #ROUTE UNTUK ENROLL
    Volt::route('enrollment', 'admin.enrollment')
        ->middleware(['permission:manage_enrollment_status-create']) 
        ->name('enrollment.index');

});

require __DIR__.'/auth.php';
