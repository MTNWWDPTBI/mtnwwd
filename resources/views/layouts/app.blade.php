<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Maintenance WWD') }}</title>
        
        <!-- FOUC Prevention Script (Anti-Flash of Unstyled Content) -->
        <script>
            (function() {
                try {
                    const savedTheme = localStorage.getItem('theme');
                    if (savedTheme === 'light') {
                        document.documentElement.classList.remove('dark');
                    } else {
                        document.documentElement.classList.add('dark');
                        if (!savedTheme) {
                            localStorage.setItem('theme', 'dark');
                        }
                    }
                } catch (e) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        
        <!-- Scripts & Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-[#181A2F] text-slate-100 selection:bg-[#B4182D] selection:text-white">
        <!-- Wrapper Utama dengan Alpine.js untuk toggle sidebar HP -->
        <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden bg-[#181A2F]">
            
            <!-- Panggil Sidebar Navigation -->
            @include('layouts.navigation')

            <!-- Area Konten Utama (Kanan) -->
            <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#181A2F]">
                
                <!-- Topbar Khusus Mobile (Muncul di HP untuk tombol buka sidebar dan toggle tema) -->
                <header class="flex items-center justify-between px-4 sm:px-6 py-3.5 bg-[#242E49] border-b border-[#37415C]/60 md:hidden z-10">
                    <button @click="sidebarOpen = true" class="text-slate-300 hover:text-[#FDA481] focus:outline-none p-1">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="text-base sm:text-lg font-extrabold tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-[#FDA481] to-[#B4182D]">
                        MAINTENANCE <span class="text-white">WWD</span>
                    </div>
                    
                    <!-- Mobile Theme Switcher Button -->
                    <button type="button" 
                            onclick="window.toggleTheme()" 
                            title="Ganti Tema (Light / Dark)"
                            class="p-2 rounded-xl bg-[#181A2F]/80 text-slate-300 hover:text-white border border-[#37415C]/60 shadow-sm transition">
                        <!-- Sun Icon (Active when dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <!-- Moon Icon (Active when light mode) -->
                        <svg class="w-5 h-5 block dark:hidden text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </header>

                <!-- Page Heading (Jika ada title header opsional) -->
                @if (isset($header))
                    <header class="bg-[#242E49]/90 backdrop-blur-md border-b border-[#37415C]/50 hidden md:block z-10">
                        <div class="max-w-7xl mx-auto py-3.5 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                            <div class="min-w-0 flex-1">
                                {{ $header }}
                            </div>
                            <!-- Theme Switcher Desktop Button -->
                            <div class="flex items-center gap-3 pl-4">
                                <button type="button" 
                                        onclick="window.toggleTheme()" 
                                        title="Ganti Tema (Light / Dark)"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#181A2F]/80 text-slate-300 hover:text-white border border-[#37415C]/60 text-xs font-semibold shadow-sm transition">
                                    <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                    </svg>
                                    <svg class="w-4 h-4 block dark:hidden text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                    </svg>
                                    <span class="hidden dark:inline text-slate-200">Mode Terang</span>
                                    <span class="inline dark:hidden text-slate-700">Mode Gelap</span>
                                </button>
                            </div>
                        </div>
                    </header>
                @else
                    <!-- Topbar Desktop Minimalis jika halaman tidak memiliki $header khusus -->
                    <header class="bg-[#242E49]/90 backdrop-blur-md border-b border-[#37415C]/50 hidden md:block z-10">
                        <div class="max-w-7xl mx-auto py-2.5 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                            <div class="flex items-center gap-2 text-xs font-semibold text-slate-400 tracking-wide uppercase">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                <span>Maintenance & Sparepart Management System</span>
                            </div>
                            <!-- Theme Switcher Desktop Button -->
                            <button type="button" 
                                    onclick="window.toggleTheme()" 
                                    title="Ganti Tema (Light / Dark)"
                                    class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-[#181A2F]/80 text-slate-300 hover:text-white border border-[#37415C]/60 text-xs font-semibold shadow-sm transition">
                                <svg class="w-4 h-4 hidden dark:block text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <svg class="w-4 h-4 block dark:hidden text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                </svg>
                                <span class="hidden dark:inline text-slate-200">Mode Terang</span>
                                <span class="inline dark:hidden text-slate-700">Mode Gelap</span>
                            </button>
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

        <!-- Global Theme Toggle Handler Script -->
        <script>
            window.toggleTheme = function() {
                const isDark = document.documentElement.classList.contains('dark');
                const newTheme = isDark ? 'light' : 'dark';
                
                if (newTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }

                try {
                    localStorage.setItem('theme', newTheme);
                } catch (e) {
                    console.error('LocalStorage unavailable:', e);
                }

                // Dispatch global event for Chart.js or other reactive components
                window.dispatchEvent(new CustomEvent('theme-changed', {
                    detail: {
                        theme: newTheme,
                        isDark: newTheme === 'dark'
                    }
                }));
            };
        </script>
    </body>
</html>
