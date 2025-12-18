<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="drawer-side">
    <label for="my-drawer-1" aria-label="close sidebar" class="drawer-overlay"></label>

    <ul class="menu bg-base-200 min-h-full w-80 p-4">

        <li class="mb-4">
            <a href="{{ route('dashboard') }}" wire:navigate
               class="text-2xl font-extrabold text-black tracking-wider px-4 hover:bg-transparent">
                LMS KAMPUS
            </a>
        </li>

        <li>
            <a href="{{ route('dashboard') }}" wire:navigate @class(['active' => request()->routeIs('dashboard')])>
                Dashboard
            </a>
        </li>

        @role('superadministrator')
        <li>
            <details {{ request()->routeIs('superadmin.*') ? 'open' : '' }}>
                <summary class="list-none [&::-webkit-details-marker]:hidden font-bold text-red-800">
                    Admin
                </summary>
                <ul>
                    <li>
                        <a href="{{ route('superadmin.tenants.index') }}" wire:navigate @class(['active' => request()->routeIs('superadmin.tenants.*')])>Manajemen Tenant</a>
                    </li>
                </ul>
            </details>
        </li>
        @endrole

        @if(auth()->check() && auth()->user()->hasPermission(['faculties-read', 'departments-read', 'study_programs-read']))
        <li>
            <details {{ request()->routeIs('admin.faculties.*', 'admin.departments.*', 'admin.study-programs.*') ? 'open' : '' }}>
                <summary class="list-none [&::-webkit-details-marker]:hidden">
                    Manajemen Unit Akademik
                </summary>
                <ul>
                    @permission('faculties-read')
                    <li>
                        <a href="{{ route('admin.faculties.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.faculties.*')])>Fakultas</a>
                    </li>
                    @endpermission

                    @permission('departments-read')
                    <li>
                        <a href="{{ route('admin.departments.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.departments.*')])>Departemen</a>
                    </li>
                    @endpermission

                    @permission('study_programs-read')
                    <li>
                        <a href="{{ route('admin.study-programs.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.study-programs.*')])>Program Studi</a>
                    </li>
                    @endpermission
                </ul>
            </details>
        </li>
        @endif

        @if(auth()->check() && auth()->user()->hasPermission(['staff_prodis-read', 'pengajars-read', 'siswas-read']))
        <li>
            <details {{ request()->routeIs('admin.staff.*', 'admin.pengajars.*', 'admin.siswas.*') ? 'open' : '' }}>
                <summary class="list-none [&::-webkit-details-marker]:hidden">
                    Manajemen Pengguna
                </summary>
                <ul>
                    @permission('staff_prodis-read')
                    <li>
                        <a href="{{ route('admin.staff.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.staff.*')])>Staff Prodi</a>
                    </li>
                    @endpermission

                    @permission('pengajars-read')
                    <li>
                        <a href="{{ route('admin.academic.users.index', ['tab' => 'dosen']) }}" wire:navigate @class(['active' => request()->routeIs('admin.academic.users.index') && request()->query('tab') === 'dosen'])>Dosen</a>
                    </li>
                    @endpermission

                    @permission('siswas-read')
                    <li>
                        <a href="{{ route('admin.academic.users.index', ['tab' => 'mahasiswa']) }}" wire:navigate @class(['active' => request()->routeIs('admin.academic.users.index') && request()->query('tab') === 'mahasiswa'])>Mahasiswa</a>
                    </li>
                    @endpermission

                    @permission('admins-read')
                    <li>
                        <a href="{{ route('admin.admins.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.admins.*')])>Admin</a>
                    </li>
                    @endpermission
                </ul>
            </details>
        </li>
        @endif

        @permission('manage-enrollment-status')
        <li>
            <a href="{{ route('admin.enrollment.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.enrollment.index')])>
                Kelola Pendaftaran
            </a>
        </li>
        @endpermission

        @permission('courses-read')
        <li>
            <a href="{{ route('courses.index') }}" wire:navigate @class(['active' => request()->routeIs('courses.index')])>
                Mata Kuliah
            </a>
        </li>
        @endpermission

        @role('superadministrator|siswa')
        <li>
            <a href="{{ route('courses.enroll.index') }}" wire:navigate @class(['active' => request()->routeIs('courses.enroll.index')])>
                Ambil Mata Kuliah
            </a>
        </li>
        <li>
            <a href="{{ route('student.transkrip') }}" wire:navigate @class(['active' => request()->routeIs('student.transkrip')])>
                Transkrip Nilai
            </a>
        </li>
        @endrole

        <div class="flex-grow"></div>

        @auth
            <li>
                <a href="{{ route('profile') }}" wire:navigate @class(['active' => request()->routeIs('profile')])>
                    {{ auth()->user()->name }} (Profile)
                </a>
            </li>
            <li>
                <a wire:click="logout" class="cursor-pointer">Log Out</a>
            </li>
        @else
            <li>
                <a href="{{ route('login') }}" wire:navigate @class(['active' => request()->routeIs('login')])>Login</a>
            </li>
        @endauth
    </ul>
</div>
