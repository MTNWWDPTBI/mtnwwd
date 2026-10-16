<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Maintenance WWD') }}</title>
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <!-- Wrapper Utama dengan Alpine.js untuk toggle sidebar HP -->
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">
            
            <!-- Panggil Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Area Konten Utama (Kanan) -->
            <div class="flex-1 flex flex-col overflow-hidden">
                
                <!-- Topbar Khusus Mobile (Muncul di HP untuk tombol buka sidebar) -->
                <header class="flex items-center justify-between px-6 py-4 bg-white border-b md:hidden">
                    <button @click="sidebarOpen = true" class="text-gray-500 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="text-xl font-bold text-blue-600">WWD</div>
                    <div class="w-6"></div> <!-- Spacer -->
                </header>

                <!-- Page Heading (Jika ada title header) -->
                @if (isset($header))
                    <header class="bg-white shadow hidden md:block">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Tempat Konten Halaman (Dashboard, Form, dll) dimuat -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>