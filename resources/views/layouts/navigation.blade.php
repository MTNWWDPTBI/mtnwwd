<!-- Layar Gelap (Backdrop) saat sidebar terbuka di HP -->
<div x-show="sidebarOpen" 
     class="fixed inset-0 z-20 bg-black bg-opacity-50 transition-opacity md:hidden" 
     @click="sidebarOpen = false" x-cloak></div>

<!-- Sidebar Container -->
<div :class="sidebarOpen ? 'translate-x-0 ease-out' : '-translate-x-full ease-in'" 
     class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r overflow-y-auto transition duration-300 transform md:translate-x-0 md:static md:inset-0 flex flex-col">
    
    <!-- Logo Aplikasi -->
    <div class="flex items-center justify-center h-16 border-b md:justify-start md:px-6">
        <a href="{{ route('dashboard') }}" class="text-2xl font-extrabold text-blue-700 tracking-tight">
            Maintenance<span class="text-gray-800">WWD</span>
        </a>
    </div>

    <!-- Daftar Menu Link -->
    <nav class="flex-1 mt-5 px-4 space-y-2">
        <!-- Menu Home -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>

        <!-- Menu Sparepart Catalog -->
        <a href="{{ route('sparepart.catalog') }}" 
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('sparepart.*') ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Sparepart
        </a>

        <!-- Menu Project (Persiapan) -->
        <a href="#" 
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Project
        </a>
        
        <!-- Menu Data Maintenance / Import (Akses Admin) -->
        <a href="{{ route('import.index') }}" 
           class="flex items-center px-4 py-3 text-sm font-medium rounded-lg {{ request()->routeIs('import.*') ? 'bg-blue-50 text-blue-700 border-l-4 border-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
            Data Master (Import)
            <a href="{{ route('machines.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
            Master Mesin
            <a href="{{ route('assemblies.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            Master Assembly
            <a href="{{ route('spareparts.index') }}" class="flex items-center px-4 py-3 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900">
            <svg class="mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            Master Sparepart
        </a>
        </a>
        </a>
        </a>
    </nav>

    <!-- Profil & Logout (Bagian Bawah Sidebar) -->
    <div class="p-4 border-t bg-gray-50">
        <div class="flex items-center">
            <div class="h-9 w-9 rounded-full bg-blue-600 flex items-center justify-center text-white font-bold uppercase">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-red-600 hover:text-red-800 mt-1">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>