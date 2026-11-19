<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MTN WWD - Bekaert Indonesia') }}</title>

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

        <style>
            /* Fix Browser Autofill Background & Text Color in Dark & Light Themes */
            html.dark input:-webkit-autofill,
            html.dark input:-webkit-autofill:hover, 
            html.dark input:-webkit-autofill:focus,
            html.dark input:-webkit-autofill:active {
                -webkit-text-fill-color: #f1f5f9 !important;
                -webkit-box-shadow: 0 0 0px 1000px #181A2F inset !important;
                box-shadow: 0 0 0px 1000px #181A2F inset !important;
                transition: background-color 5000s ease-in-out 0s;
                caret-color: #f1f5f9;
            }

            html:not(.dark) input:-webkit-autofill,
            html:not(.dark) input:-webkit-autofill:hover, 
            html:not(.dark) input:-webkit-autofill:focus,
            html:not(.dark) input:-webkit-autofill:active {
                -webkit-text-fill-color: #1e293b !important;
                -webkit-box-shadow: 0 0 0px 1000px #f8fafc inset !important;
                box-shadow: 0 0 0px 1000px #f8fafc inset !important;
                transition: background-color 5000s ease-in-out 0s;
                caret-color: #1e293b;
            }
        </style>
    </head>
    <body class="font-sans antialiased text-slate-800 dark:text-slate-100 bg-[#f1f5f9] dark:bg-[#181A2F] transition-colors duration-200 selection:bg-[#B4182D] selection:text-white">
        
        <!-- Background Wrapper with Ambient Bekaert Industrial Glow -->
        <div class="relative min-h-screen flex flex-col justify-center items-center p-4 sm:p-6 overflow-hidden">
            
            <!-- Ambient Glow Spots matching Dashboard -->
            <div class="absolute -top-24 -left-24 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-[#B4182D]/20 dark:bg-[#B4182D]/25 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 sm:w-96 h-80 sm:h-96 rounded-full bg-[#FDA481]/15 dark:bg-[#FDA481]/15 blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[550px] h-[550px] rounded-full bg-[#54162B]/10 dark:bg-[#242E49]/40 blur-3xl pointer-events-none -z-10"></div>

            <!-- Top Right Floating Theme Switcher -->
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-30">
                <button type="button" 
                        onclick="window.toggleTheme()" 
                        title="Ganti Tema (Light / Dark)"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/90 dark:bg-[#242E49]/90 backdrop-blur-md text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-[#37415C]/80 shadow-md hover:border-[#FDA481]/50 dark:hover:border-[#FDA481]/50 text-xs font-semibold transition-all duration-200 group">
                    <!-- Sun Icon (Active in Dark Mode) -->
                    <svg class="w-4 h-4 hidden dark:block text-amber-400 group-hover:rotate-45 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <!-- Moon Icon (Active in Light Mode) -->
                    <svg class="w-4 h-4 block dark:hidden text-slate-600 group-hover:-rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <span class="hidden dark:inline font-medium text-slate-200">Mode Terang</span>
                    <span class="inline dark:hidden font-medium text-slate-700">Mode Gelap</span>
                </button>
            </div>

            <!-- Main Form Card Container -->
            <div class="relative z-10 w-full max-w-md">
                {{ $slot }}
            </div>

            <!-- Footer Watermark / Copyright -->
            <div class="mt-7 text-center text-xs text-slate-500 dark:text-slate-400 relative z-10 font-medium">
                &copy; {{ date('Y') }} PT Bekaert Indonesia &bull; Maintenance WWD Plant
            </div>

        </div>

        <!-- Global Theme Toggle Script -->
        <script>
            window.toggleTheme = function() {
                const isDark = document.documentElement.classList.contains('dark');
                const newTheme = isDark ? 'light' : 'dark';
                
                if (newTheme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                
                localStorage.setItem('theme', newTheme);
                window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme: newTheme } }));
            };
        </script>
    </body>
</html>
