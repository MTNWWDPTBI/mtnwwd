<!-- Layar Gelap (Backdrop) saat sidebar terbuka di HP -->
<div x-show="sidebarOpen" 
     class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm transition-opacity md:hidden" 
     @click="sidebarOpen = false" 
     x-cloak></div>

<!-- Sidebar Container (Material Design 3 Navigation Drawer) -->
<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" 
     class="fixed inset-y-0 left-0 z-50 w-72 bg-[#181A2F] border-r border-[#37415C]/60 overflow-y-auto transition-transform duration-300 transform md:translate-x-0 md:static md:inset-0 flex flex-col justify-between shadow-2xl md:shadow-none">
    
    <div>
        <!-- Logo Aplikasi & Brand Header -->
        <div class="flex items-center justify-between h-20 px-6 border-b border-[#37415C]/50 bg-[#181A2F]">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-[#B4182D] via-[#54162B] to-[#FDA481] flex items-center justify-center shadow-md group-hover:scale-105 transition-transform duration-300">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <div class="text-lg font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-[#FDA481] to-[#B4182D]">
                        MTN WWD
                    </div>
                    <div class="text-[10px] tracking-widest uppercase font-semibold text-slate-400">
                        Bekaert Indonesia
                    </div>
                </div>
            </a>

            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Daftar Menu Navigasi -->
        <nav class="mt-6 px-4 space-y-1.5">
            <div class="px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                Menu Utama
            </div>

            <!-- 1. Home / Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D] shadow-sm' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- 2. Katalog Sparepart -->
            <a href="{{ route('sparepart.catalog') }}" 
               class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition duration-200 {{ request()->routeIs('sparepart.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D] shadow-sm' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('sparepart.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Katalog Sparepart</span>
            </a>

            <!-- 3. Stock TPM -->
            <a href="{{ route('tpm.index') }}" 
                class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition duration-200 {{ request()->routeIs('tpm.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D] shadow-sm' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('tpm.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>TPM</span>
            </a>

            <!-- 4. Shift Handover -->
            <a href="{{ route('handover.index') }}" 
            class="flex items-center gap-3 px-4 py-3 text-sm font-semibold rounded-xl transition duration-200 {{ request()->routeIs('handover.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D] shadow-sm' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('handover.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span>Shift Handover</span>
            </a>

            <!-- Group: Master Data & Admin -->
            <div class="pt-6 px-3 pb-2 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                Kelola Data Master
            </div>

            <!-- 3. Master Mesin -->
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('machines.index') }}" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition duration-200 {{ request()->routeIs('machines.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D]' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('machines.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <span>Data Mesin</span>
            </a>
            @endif

            <!-- 4. Master Assembly -->
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('assemblies.index') }}" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition duration-200 {{ request()->routeIs('assemblies.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D]' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('assemblies.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a１ １ ０ ０１１ １v６a１ １ ０ ０１－１ １h－２a１ １ ０ ０１－１－１v－６z"/>
                </svg>
                <span>Data Assembly</span>
            </a>
            @endif

            <!-- 5. Master Sparepart -->
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('spareparts.index') }}" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition duration-200 {{ request()->routeIs('spareparts.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D]' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('spareparts.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span>Master Sparepart</span>
            </a>
            @endif

            <!-- 6. Import Data Excel -->
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('import.index') }}" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition duration-200 {{ request()->routeIs('import.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D]' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('import.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                <span>Import Excel (BOM)</span>
            </a>
            @endif

            <!-- 7. Manajemen Akun (Hanya untuk Admin) -->
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('users.index') }}" 
               class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium rounded-xl transition duration-200 {{ request()->routeIs('users.*') ? 'bg-[#242E49] text-[#FDA481] border-l-4 border-[#B4182D]' : 'text-slate-300 hover:bg-[#242E49]/60 hover:text-white' }}">
                <svg class="w-4 h-4 {{ request()->routeIs('users.*') ? 'text-[#FDA481]' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Manajemen Akun</span>
            </a>
            @endif
        </nav>
    </div>

    <!-- Profil Pengguna & Tombol Logout (Material 3 Surface) -->
    <div class="p-4 m-3 rounded-2xl bg-[#242E49]/90 border border-[#37415C]/60 shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3 min-w-0">
                <div class="h-10 w-10 flex-shrink-0 rounded-xl bg-gradient-to-tr from-[#B4182D] to-[#FDA481] flex items-center justify-center text-white font-bold text-sm shadow-md">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-white truncate">{{ Auth::user()->name ?? 'Operator' }}</p>
                    <span class="inline-flex items-center text-[10px] text-[#FDA481] font-medium">Online</span>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                @csrf
                <button type="submit" 
                        title="Log Out"
                        class="p-2 rounded-lg text-slate-400 hover:text-white hover:bg-[#B4182D]/80 transition duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

</div>