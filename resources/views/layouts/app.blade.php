{{-- <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            <livewire:layout.navigation />

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                
                <ul class="menu bg-base-200 min-h-full w-65 p-4">
                    
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
                    <li>
                        <a href="{{ route('admin.courses.index') }}" wire:navigate @class(['active' => request()->routeIs('admin.courses.index')])>
                            Courses
                        </a>
                    </li>

                    <div class="flex-grow"></div>
                    
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
                </ul>
            </div>
        </div>
    </body>
</html>
