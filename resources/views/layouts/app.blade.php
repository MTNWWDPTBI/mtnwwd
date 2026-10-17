<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Maintenance WWD') }}</title>
        
        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#181A2F] text-slate-100 selection:bg-[#B4182D] selection:text-white">
        <!-- Wrapper Utama dengan Alpine.js untuk toggle sidebar HP -->
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-[#181A2F]">
            
            <!-- Panggil Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Area Konten Utama (Kanan) -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#181A2F]">
                
                <!-- Topbar Khusus Mobile (Muncul di HP untuk tombol buka sidebar) -->
                <header class="flex items-center justify-between px-6 py-4 bg-[#242E49] border-b border-[#37415C]/60 md:hidden z-10">
                    <button @click="sidebarOpen = true" class="text-slate-300 hover:text-[#FDA481] focus:outline-none p-1">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="text-lg font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-[#FDA481] to-[#B4182D]">
                        MAINTENANCE <span class="text-white">WWD</span>
                    </div>
                    <div class="w-6"></div> <!-- Spacer -->
                </header>

                <!-- Page Heading (Jika ada title header opsional) -->
                @if (isset($header))
                    <header class="bg-[#242E49]/80 backdrop-blur-md border-b border-[#37415C]/50 hidden md:block">
                        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Tempat Konten Halaman (Dashboard, Katalog, Form, dll) dimuat -->
                <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#181A2F] p-4 sm:p-6 lg:p-8">
                    <div class="max-w-7xl mx-auto">
                        {{ $slot }}
                    </div>
                </main>
            </div>
        </div>
    </body>
</html>