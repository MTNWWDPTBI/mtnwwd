<x-app-layout>
    <div class="space-y-8 pb-12">
        
        <!-- 1. Hero Banner Material Design 3 -->
        <div class="relative overflow-hidden rounded-[28px] m3-gradient-banner p-6 sm:p-10 border border-[#37415C]/50 shadow-m3-3">
            <!-- Ambient Glow Spots -->
            <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-[#B4182D]/25 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -left-20 w-80 h-80 rounded-full bg-[#FDA481]/15 blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 max-w-4xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold m3-badge-peach mb-4">
                    <span class="w-2 h-2 rounded-full bg-[#FDA481] animate-ping"></span>
                    <span>Sistem Pemeliharaan & Katalog Terpadu WWD</span>
                </div>
                
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight leading-tight">
                    Pusat Kendali Mesin & <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#FDA481] to-[#B4182D]">Suku Cadang</span>
                </h1>
                
                <p class="mt-3 text-slate-300 text-sm sm:text-base max-w-2xl leading-relaxed">
                    Pilih tipe mesin operasional di bawah untuk melihat suku cadang (*sparepart*), komponen *assembly* (BOM), dan posisi rak (*bin location*) di gudang.
                </p>

                <!-- Floating Search Bar Material Design -->
                <form action="{{ route('sparepart.catalog') }}" method="GET" class="mt-6 max-w-2xl">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" 
                               name="search" 
                               placeholder="Cari suku cadang langsung (misal: Loctite, Flange, Clamp, Kode SAP)..." 
                               class="w-full pl-11 pr-32 py-3.5 bg-[#181A2F]/90 backdrop-blur-md border border-[#37415C] text-white placeholder-slate-400 rounded-full text-sm focus:outline-none focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/40 transition shadow-inner">
                        <button type="submit" 
                                class="absolute right-1.5 px-5 py-2 rounded-full bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                            Cari Part
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. Material 3 Quick Metric Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Metric 1: Total Mesin -->
            <div class="bg-[#242E49] border border-[#37415C]/60 rounded-[22px] p-5 shadow-m3-1 hover:border-[#FDA481]/40 transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Tipe Mesin</span>
                    <div class="w-10 h-10 rounded-xl bg-[#FDA481]/15 text-[#FDA481] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['total_machines'] }}</span>
                    <span class="text-xs text-[#FDA481] font-medium">Unit Terdaftar</span>
                </div>
            </div>

            <!-- Metric 2: Total Assemblies -->
            <div class="bg-[#242E49] border border-[#37415C]/60 rounded-[22px] p-5 shadow-m3-1 hover:border-[#FDA481]/40 transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Sub-Assembly</span>
                    <div class="w-10 h-10 rounded-xl bg-[#B4182D]/20 text-[#FDA481] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ $stats['total_assemblies'] }}</span>
                    <span class="text-xs text-slate-400 font-medium">BOM Groups</span>
                </div>
            </div>

            <!-- Metric 3: Total Spareparts -->
            <div class="bg-[#242E49] border border-[#37415C]/60 rounded-[22px] p-5 shadow-m3-1 hover:border-[#FDA481]/40 transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Katalog Part</span>
                    <div class="w-10 h-10 rounded-xl bg-[#54162B] text-[#FDA481] border border-[#B4182D]/40 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-white">{{ number_format($stats['total_spareparts']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">Item Master</span>
                </div>
            </div>

            <!-- Metric 4: Lokasi Bin Terdata -->
            <div class="bg-[#242E49] border border-[#37415C]/60 rounded-[22px] p-5 shadow-m3-1 hover:border-[#FDA481]/40 transition duration-300">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Lokasi Terdata</span>
                    <div class="w-10 h-10 rounded-xl bg-[#FDA481]/20 text-[#FDA481] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold text-[#FDA481]">{{ number_format($stats['total_locations']) }}</span>
                    <span class="text-xs text-slate-400 font-medium">Bin Rak Aktif</span>
                </div>
            </div>
        </div>

        <!-- 3. Machine Type Cards Grid (Material Design 3) -->
        <div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-white tracking-tight flex items-center gap-2">
                        <span>Tipe-Tipe Mesin WWD</span>
                        <span class="text-xs font-normal px-2.5 py-0.5 rounded-full bg-[#37415C] text-[#FDA481]">
                            {{ $machines->count() }} Mesin
                        </span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-1">
                        Klik pada kartu mesin untuk langsung membuka daftar suku cadang yang digunakan pada mesin tersebut.
                    </p>
                </div>

                <a href="{{ route('sparepart.catalog') }}" 
                   class="self-start sm:self-auto inline-flex items-center gap-1.5 text-xs font-semibold text-[#FDA481] hover:text-white transition">
                    <span>Lihat Semua Katalog Sparepart</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Grid Kartu Mesin Interaktif -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($machines as $machine)
                    <a href="{{ route('sparepart.catalog', ['machine_id' => $machine->id]) }}" 
                       class="group m3-surface overflow-hidden flex flex-col justify-between text-left block focus:outline-none focus:ring-2 focus:ring-[#FDA481]">
                        
                        <!-- Area Gambar Mesin dengan Badge Kode Mesin -->
                        <div class="relative w-full h-48 bg-[#181A2F] overflow-hidden flex items-center justify-center border-b border-[#37415C]/50">
                            <!-- Machine Image Illustration -->
                            <img src="{{ $machine->image_url }}" 
                                 alt="{{ $machine->machine_name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                            
                            <!-- Overlay Gradient Vignette -->
                            <div class="absolute inset-0 bg-gradient-to-t from-[#242E49] via-transparent to-black/20 pointer-events-none"></div>

                            <!-- Top-Right Machine Code Chip (Material 3 Pill) -->
                            <div class="absolute top-3 right-3">
                                <span class="px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-[#181A2F]/85 backdrop-blur-md text-[#FDA481] border border-[#FDA481]/30 shadow-md">
                                    {{ $machine->machine_code }}
                                </span>
                            </div>

                            <!-- Bottom-Left Status Indicator -->
                            <div class="absolute bottom-3 left-3 flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#181A2F]/80 backdrop-blur-md text-emerald-400 border border-emerald-500/20">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                <span>Aktif</span>
                            </div>
                        </div>

                        <!-- Area Konten & Deskripsi Mesin -->
                        <div class="p-6 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white group-hover:text-[#FDA481] transition duration-200">
                                    {{ $machine->machine_name }}
                                </h3>
                                
                                <p class="text-xs text-slate-400 mt-1 line-clamp-1">
                                    ID Mesin: #{{ $machine->id }} &bull; Kode Mesin: {{ $machine->machine_code }}
                                </p>

                                <!-- Sub-Assembly & Sparepart Pills -->
                                <div class="flex flex-wrap gap-2 mt-4">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[#181A2F] text-slate-300 border border-[#37415C]">
                                        <svg class="w-3.5 h-3.5 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>{{ $machine->assemblies_count }} Assembly</span>
                                    </span>

                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[#181A2F] text-[#FDA481] border border-[#FDA481]/30">
                                        <svg class="w-3.5 h-3.5 text-[#B4182D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>{{ $machine->spareparts_count }} Part Terhubung</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Card Footer Button Action -->
                            <div class="mt-6 pt-4 border-t border-[#37415C]/60 flex items-center justify-between text-xs font-semibold text-[#FDA481]">
                                <span class="group-hover:text-white transition">Buka Suku Cadang</span>
                                <div class="w-8 h-8 rounded-full bg-[#181A2F] border border-[#37415C] flex items-center justify-center text-[#FDA481] group-hover:bg-[#B4182D] group-hover:border-[#B4182D] group-hover:text-white transition-all duration-300">
                                    <svg class="w-4 h-4 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </a>
                @endforeach
            </div>
        </div>

    </div>
</x-app-layout>