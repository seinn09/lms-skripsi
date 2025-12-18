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
                <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8 flex items-center justify-between">

                    <div class="flex items-center">
                        <label for="my-drawer-1" class="btn btn-ghost btn-circle drawer-button lg:hidden">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </label>

                        @if (isset($header))
                            <div class="flex-1 ml-2">
                                {{ $header }}
                            </div>
                        @endif
                    </div>

                </div>
            </header>

            <main class="flex-1 p-6">
                {{ $slot }}
            </main>
        </div>

        <livewire:layout.sidebar />

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
                    timer: 3000,
                    timerProgressBar: true,
                });
            });
        </script>
    @endif

</body>
</html>
