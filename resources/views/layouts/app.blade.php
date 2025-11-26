<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body class="font-sans antialiased">
        
        <div class="drawer lg:drawer-open min-h-screen bg-gray-100">
            <input id="my-drawer-1" type="checkbox" class="drawer-toggle" />

            <div class="drawer-content flex flex-col">
                
                <header class="bg-white shadow sticky top-0 z-10">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center">
                        
                        <label for="my-drawer-1" class="btn btn-ghost btn-circle drawer-button lg:hidden"> <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </label>

                        @if (isset($header))
                            <div class="flex-1 ml-2">
                                {{ $header }}
                            </div>
                        @endif
                    </div>
                </header>

                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>
            </div>

            <div class="drawer-side">
                <label for="my-drawer-1" aria-label="close sidebar" class="drawer-overlay"></label>
                
                <ul class="menu bg-base-200 min-h-full w-72 p-4">
                    
                    <li class="mb-2">
                        <a href="{{ route('dashboard') }}" wire:navigate class="text-xl font-bold">
                            <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                            {{ config('app.name', 'Laravel') }}
                        </a>
                    </li>
                    
                    <li>
                        <a href="{{ route('dashboard') }}" wire:navigate @class(['active' => request()->routeIs('dashboard')])>
                            Dashboard
                        </a>
                    </li>

                    @if(auth()->check() && auth()->user()->hasPermission(['staff_prodis-read', 'pengajars-read', 'siswas-read']))
                    <li>
                        <details {{ request()->routeIs('admin.staff.*', 'admin.pengajars.*', 'admin.siswas.*') ? 'open' : '' }}>
                            
                            <summary class="list-none [&::-webkit-details-marker]:hidden">
                                Manajemen Pengguna
                            </summary>
                            
                            <ul>
                                @permission('staff_prodis-read')
                                <li>
                                    <a href="{{ route('admin.staff.index') }}" wire:navigate 
                                    @class(['active' => request()->routeIs('admin.staff.*')])>
                                        Staff Prodi
                                    </a>
                                </li>
                                @endpermission

                                @permission('pengajars-read')
                                <li>
                                    <a href="{{ route('admin.academic.users.index', ['tab' => 'dosen']) }}" wire:navigate 
                                    @class(['active' => request()->routeIs('admin.pengajars.*')])>
                                        Dosen
                                    </a>
                                </li>
                                @endpermission

                                @permission('siswas-read')
                                <li>
                                    <a href="{{ route('admin.academic.users.index', ['tab' => 'mahasiswa']) }}" wire:navigate 
                                    @class(['active' => request()->routeIs('admin.siswas.*')])>
                                        Mahasiswa
                                    </a>
                                </li>
                                @endpermission
                            </ul>

                        </details>
                    </li>
                    @endif

                    @permission('manage_enrollment_status-read')
                        <li>
                            <a href="{{ route('admin.enrollment.index') }}" wire:navigate 
                            @class(['active' => request()->routeIs('admin.enrollment.index')])>
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

                    @role('superadministrator|admin|siswa')
                        <li>
                            <a href="{{ route('courses.enroll.index') }}" wire:navigate 
                            @class(['active' => request()->routeIs('courses.enroll.index')])>
                                Ambil Mata Kuliah
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
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <a href="{{ route('logout') }}"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();"
                                class="cursor-pointer">
                                    Log Out
                                </a>
                            </form>
                        </li>
                    @else
                        <li>
                            <a href="{{ route('login') }}" wire:navigate @class(['active' => request()->routeIs('login')])>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h5a3 3 0 013 3v1" /></svg>
                                Login
                            </a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>

        @if (session()->has('notify'))
            <script>
                document.addEventListener('livewire:navigated', () => {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: '{{ session('notify')['type'] }}',
                        title: '{{ session('notify')['message'] }}',
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                });
            </script>
        @endif
    </body>
</html>
