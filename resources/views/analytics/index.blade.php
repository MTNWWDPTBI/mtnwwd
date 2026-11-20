<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-extrabold text-xl sm:text-2xl text-white tracking-wide flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-gradient-to-tr from-[#B4182D] to-[#FDA481] text-white shadow-md">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </span>
                    <span>Reliability & Breakdown Analytics</span>
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Visualisasi performa mesin, histori downtime SAP, MTBF, MTTR, dan analisis komponen bermasalah
                </p>
            </div>

            <div class="flex items-center flex-wrap gap-2 text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#242E49] border border-[#37415C]/60 text-slate-300">
                    <svg class="w-4 h-4 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>{{ $startDate->translatedFormat('d M Y') }} – {{ $endDate->translatedFormat('d M Y') }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-[#242E49] border border-[#37415C]/60 text-emerald-400 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span>{{ $kpis['machine_count'] }} Mesin Scope</span>
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 px-3 sm:px-6 max-w-7xl mx-auto space-y-6">

        <!-- ========================================== -->
        <!-- 1. FILTER BAR INTERAKTIF                   -->
        <!-- ========================================== -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-[#242E49] border border-slate-200 dark:border-[#37415C]/60 shadow-sm dark:shadow-lg transition-colors">
            <form id="analyticsFilterForm" method="GET" action="{{ route('analytics.index') }}" class="space-y-4">
                <input type="hidden" name="period" id="filterPeriod" value="{{ $activePeriod }}">

                <!-- Row 1: Filter Scope Mesin, PIC, dan Bulan SAP (3 Kolom Seimbang) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <!-- 1. Filter PIC Mesin -->
                    <div>
                        <label for="picSelect" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-[#FDA481] mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#B4182D] dark:text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>PIC Mesin</span>
                        </label>
                        <select name="pic" id="picSelect" onchange="submitFilter()" 
                                class="w-full bg-slate-50 dark:bg-[#181A2F] border border-slate-300 dark:border-[#37415C] rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-[#B4182D] dark:focus:ring-[#FDA481] focus:border-transparent transition shadow-sm">
                            <option value="all" {{ $selectedPic === 'all' ? 'selected' : '' }}>
                                🌐 Semua PIC (Seluruh Mesin WWD)
                            </option>
                            @foreach ($pics as $picUser)
                                <option value="{{ $picUser->id }}" {{ (string) $selectedPic === (string) $picUser->id ? 'selected' : '' }}>
                                    👤 {{ $picUser->name }}{{ $picUser->id === $currentUser->id ? ' (Saya)' : '' }} ({{ $picUser->picMachines->count() }} Mesin)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Filter Mesin Spesifik -->
                    <div>
                        <label for="machineSelect" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-[#FDA481] mb-1.5 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#B4182D] dark:text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <span>Mesin Spesifik</span>
                        </label>
                        <select name="machine" id="machineSelect" onchange="submitFilter()" 
                                class="w-full bg-slate-50 dark:bg-[#181A2F] border border-slate-300 dark:border-[#37415C] rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-[#B4182D] dark:focus:ring-[#FDA481] focus:border-transparent transition shadow-sm">
                            <option value="all" {{ $selectedMachine === 'all' ? 'selected' : '' }}>
                                ⚙️ {{ $selectedPic !== 'all' ? 'Semua Mesin PIC Terpilih' : 'Semua Mesin' }}
                            </option>
                            @foreach ($availableMachines as $mLoc)
                                <option value="{{ $mLoc }}" {{ $selectedMachine === $mLoc ? 'selected' : '' }}>
                                    {{ $mLoc }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Periode Bulan (Sama dengan Laporan PIC) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="monthInput" class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-[#FDA481] flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-[#B4182D] dark:text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>Pilih Bulan (SAP)</span>
                            </label>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-semibold border border-emerald-200 dark:border-emerald-500/30">
                                Sinkron PIC Report
                            </span>
                        </div>
                        <input type="month" name="month" id="monthInput" value="{{ $selectedMonth }}" 
                               onchange="submitMonthFilter()"
                               class="w-full bg-slate-50 dark:bg-[#181A2F] border border-slate-300 dark:border-[#37415C] rounded-xl px-3.5 py-2.5 text-xs sm:text-sm text-slate-900 dark:text-white focus:ring-2 focus:ring-[#B4182D] dark:focus:ring-[#FDA481] dark:[color-scheme:dark] transition shadow-sm">
                    </div>

                </div>

                <!-- Row 2: Periode Cepat, Custom Date Range, & Action Buttons -->
                <div class="pt-4 border-t border-slate-200 dark:border-[#37415C]/60 flex flex-col xl:flex-row xl:items-center justify-between gap-4">
                    
                    <!-- Left: Periode Cepat Pills & Rentang Tanggal -->
                    <div class="flex flex-wrap items-center gap-3">
                        
                        <!-- Pills -->
                        <div class="flex items-center flex-wrap gap-1.5">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 mr-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span>Periode:</span>
                            </span>
                            
                            <button type="button" onclick="selectQuickPeriod('7d')" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition duration-200 {{ $activePeriod === '7d' ? 'bg-gradient-to-r from-[#B4182D] to-[#FDA481] text-white shadow-md shadow-[#B4182D]/20 border border-transparent' : 'bg-slate-100 dark:bg-[#181A2F] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#37415C]/60 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-[#37415C]' }}">
                                7 Hari
                            </button>

                            <button type="button" onclick="selectQuickPeriod('30d')" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition duration-200 {{ $activePeriod === '30d' ? 'bg-gradient-to-r from-[#B4182D] to-[#FDA481] text-white shadow-md shadow-[#B4182D]/20 border border-transparent' : 'bg-slate-100 dark:bg-[#181A2F] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#37415C]/60 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-[#37415C]' }}">
                                30 Hari (Default)
                            </button>

                            <button type="button" onclick="selectQuickPeriod('month')" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition duration-200 {{ $activePeriod === 'month' ? 'bg-gradient-to-r from-[#B4182D] to-[#FDA481] text-white shadow-md shadow-[#B4182D]/20 border border-transparent' : 'bg-slate-100 dark:bg-[#181A2F] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#37415C]/60 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-[#37415C]' }}">
                                Bulan Ini
                            </button>

                            <button type="button" onclick="selectQuickPeriod('year')" 
                                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition duration-200 {{ $activePeriod === 'year' ? 'bg-gradient-to-r from-[#B4182D] to-[#FDA481] text-white shadow-md shadow-[#B4182D]/20 border border-transparent' : 'bg-slate-100 dark:bg-[#181A2F] text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-[#37415C]/60 hover:text-slate-900 dark:hover:text-white border border-slate-300 dark:border-[#37415C]' }}">
                                Tahun Ini
                            </button>
                        </div>

                        <!-- Divider Vertical -->
                        <div class="hidden sm:block h-6 w-px bg-slate-300 dark:bg-[#37415C]/70"></div>

                        <!-- Rentang Tanggal Input Box -->
                        <div class="flex items-center flex-wrap gap-2">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Rentang:</span>
                            <div class="flex items-center gap-1.5 bg-slate-50 dark:bg-[#181A2F] border border-slate-300 dark:border-[#37415C] rounded-xl px-2.5 py-1 text-xs shadow-sm">
                                <label for="startDateInput" class="text-slate-500 dark:text-slate-400 text-[11px] font-medium">Dari</label>
                                <input type="date" name="start_date" id="startDateInput" value="{{ $startDate->toDateString() }}" 
                                       class="bg-transparent border-0 p-0 text-xs text-slate-800 dark:text-slate-100 focus:ring-0 w-28 sm:w-32 dark:[color-scheme:dark]">
                                <span class="text-slate-400 dark:text-slate-500 text-xs font-bold">s/d</span>
                                <label for="endDateInput" class="text-slate-500 dark:text-slate-400 text-[11px] font-medium">Sampai</label>
                                <input type="date" name="end_date" id="endDateInput" value="{{ $endDate->toDateString() }}" 
                                       class="bg-transparent border-0 p-0 text-xs text-slate-800 dark:text-slate-100 focus:ring-0 w-28 sm:w-32 dark:[color-scheme:dark]">
                            </div>
                        </div>

                    </div>

                    <!-- Right: Action Buttons -->
                    <div class="flex items-center gap-2.5 self-end xl:self-auto shrink-0">
                        <a href="{{ route('analytics.index') }}" 
                           class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white bg-slate-100 dark:bg-[#181A2F] border border-slate-300 dark:border-[#37415C] rounded-xl hover:bg-slate-200 dark:hover:bg-[#37415C]/60 transition shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            <span>Reset Filter</span>
                        </a>

                        <button type="button" onclick="applyCustomDate()" 
                                class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-gradient-to-r from-[#B4182D] to-[#FDA481] hover:brightness-110 active:scale-95 rounded-xl shadow-md shadow-[#B4182D]/20 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>

        <!-- ========================================== -->
        <!-- 2. ENAM (6) KARTU KPI UTAMA                -->
        <!-- ========================================== -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            
            <!-- 1. Total Breakdown -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Breakdown</span>
                    <span class="p-2 rounded-xl bg-rose-500/15 text-rose-400 border border-rose-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {{ number_format($kpis['total_breakdown']) }}
                    <span class="text-xs font-semibold text-rose-400">WO</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Frekuensi order kerusakan</div>
            </div>

            <!-- 2. Total Downtime -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Downtime</span>
                    <span class="p-2 rounded-xl bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {{ number_format($kpis['total_downtime'], 1) }}
                    <span class="text-xs font-semibold text-[#FDA481]">Jam</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Total jam mesin breakdown</div>
            </div>

            <!-- 3. MTBF -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" title="Mean Time Between Failures">MTBF</span>
                    <span class="p-2 rounded-xl bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {{ number_format($kpis['mtbf'], 1) }}
                    <span class="text-xs font-semibold text-emerald-400">Jam</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Rerata interval antar rusak</div>
            </div>

            <!-- 4. MTTR -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400" title="Mean Time To Repair">MTTR</span>
                    <span class="p-2 rounded-xl bg-sky-500/15 text-sky-400 border border-sky-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {{ number_format($kpis['mttr'], 1) }}
                    <span class="text-xs font-semibold text-sky-400">Jam</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Rerata waktu perbaikan</div>
            </div>

            <!-- 5. Machine Availability -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Availability</span>
                    <span class="p-2 rounded-xl {{ $kpis['availability'] >= 95 ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30' : ($kpis['availability'] >= 85 ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'bg-rose-500/15 text-rose-400 border border-rose-500/30') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black tracking-tight {{ $kpis['availability'] >= 95 ? 'text-emerald-400' : ($kpis['availability'] >= 85 ? 'text-amber-400' : 'text-rose-400') }}">
                    {{ number_format($kpis['availability'], 1) }}%
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Kesiapan mesin beroperasi</div>
            </div>

            <!-- 6. Avg Response Time -->
            <div class="p-4 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg relative overflow-hidden group hover:border-[#FDA481]/50 transition duration-300">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Response Time</span>
                    <span class="p-2 rounded-xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                </div>
                <div class="text-xl sm:text-2xl font-black text-white tracking-tight">
                    {{ number_format($kpis['avg_response_time'], 1) }}
                    <span class="text-xs font-semibold text-indigo-400">Jam</span>
                </div>
                <div class="text-[11px] text-slate-400 mt-1">Rerata respons penanganan</div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- 3. VISUALISASI GRAFIK INTERAKTIF (2x2)     -->
        <!-- ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            
            <!-- Chart 1: Top 10 Mesin Breakdown Terlama (Jam) -->
            <div class="p-5 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#B4182D]"></span>
                            <span>Top 10 Mesin Breakdown Terlama</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Total akumulasi jam breakdown (bm_hrs)</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-[#181A2F] text-slate-300 border border-[#37415C]">
                        Satuan: Jam
                    </span>
                </div>
                <div class="relative h-72 sm:h-80 w-full">
                    <canvas id="topDowntimeChart"></canvas>
                </div>
            </div>

            <!-- Chart 2: Top 10 Mesin Paling Sering Rusak (Frekuensi) -->
            <div class="p-5 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span>Top 10 Mesin Paling Sering Rusak</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Jumlah kejadian kerusakan (Order/WO)</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-[#181A2F] text-slate-300 border border-[#37415C]">
                        Satuan: Kejadian
                    </span>
                </div>
                <div class="relative h-72 sm:h-80 w-full">
                    <canvas id="topFrequencyChart"></canvas>
                </div>
            </div>

            <!-- Chart 3: Tren Bulanan Downtime & MTTR (6 Bulan Terakhir) -->
            <div class="p-5 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-[#FDA481]"></span>
                            <span>Tren Bulanan Downtime & MTTR</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Riwayat performa 6 bulan terakhir</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="inline-flex items-center gap-1 text-[#FDA481]">
                            <span class="w-3 h-2 rounded-sm bg-[#FDA481]"></span> Downtime (Jam)
                        </span>
                        <span class="inline-flex items-center gap-1 text-sky-400">
                            <span class="w-3 h-0.5 bg-sky-400"></span> MTTR (Jam)
                        </span>
                    </div>
                </div>
                <div class="relative h-72 sm:h-80 w-full">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>

            <!-- Chart 4: Top Komponen / Object Part Paling Sering Rusak -->
            <div class="p-5 rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-sm sm:text-base text-white flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-violet-400"></span>
                            <span>Top Komponen / Part Bermasalah</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Bagian mesin paling dominan mengalami kegagalan</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-[#181A2F] text-slate-300 border border-[#37415C]">
                        Horizontal Bar
                    </span>
                </div>
                <div class="relative h-72 sm:h-80 w-full">
                    <canvas id="topComponentsChart"></canvas>
                </div>
            </div>

        </div>

        <!-- ========================================== -->
        <!-- 4. TABEL DETAIL RINGKASAN PERFORMA MESIN   -->
        <!-- ========================================== -->
        <div class="rounded-2xl bg-[#242E49] border border-[#37415C]/60 shadow-lg overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-[#37415C]/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-base text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Tabel Detail Kehandalan Mesin (Reliability Summary)</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Menampilkan metrik kehandalan komprehensif per unit mesin dalam scope terpilih
                    </p>
                </div>

                <!-- Client-side Search -->
                <div class="flex items-center gap-3">
                    <div class="relative w-full sm:w-64">
                        <input type="text" id="tableSearchInput" placeholder="Cari kode mesin..." 
                               class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl pl-9 pr-3 py-1.5 text-xs text-white placeholder-slate-400 focus:ring-2 focus:ring-[#FDA481]">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <span class="text-xs px-2.5 py-1.5 rounded-xl bg-[#181A2F] text-slate-300 border border-[#37415C] whitespace-nowrap">
                        Total: {{ $machineTable->count() }} Mesin
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs sm:text-sm">
                    <thead>
                        <tr class="bg-[#181A2F]/80 border-b border-[#37415C]/60 text-slate-300 font-bold uppercase text-[11px] tracking-wider">
                            <th class="py-3 px-4">No</th>
                            <th class="py-3 px-4">Kode Mesin</th>
                            <th class="py-3 px-4 text-center">Breakdown (Kali)</th>
                            <th class="py-3 px-4 text-right">Downtime (Jam)</th>
                            <th class="py-3 px-4 text-right">MTTR (Jam)</th>
                            <th class="py-3 px-4 text-right">MTBF (Jam)</th>
                            <th class="py-3 px-4 text-right">Availability (%)</th>
                            <th class="py-3 px-4 text-right">Avg Response (Jam)</th>
                            <th class="py-3 px-4 text-center">Status Kehandalan</th>
                        </tr>
                    </thead>
                    <tbody id="machineTableBody" class="divide-y divide-[#37415C]/40 text-slate-200">
                        @forelse ($machineTable as $index => $row)
                            <tr class="hover:bg-[#181A2F]/50 transition machine-row">
                                <td class="py-3 px-4 text-slate-400 text-xs">{{ $index + 1 }}</td>
                                <td class="py-3 px-4 font-mono font-bold text-white machine-code">
                                    {{ $row['functional_location'] }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-bold {{ $row['breakdown'] > 0 ? 'bg-rose-500/10 text-rose-400' : 'bg-slate-700/40 text-slate-400' }}">
                                        {{ $row['breakdown'] }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right font-semibold {{ $row['downtime'] > 0 ? 'text-[#FDA481]' : 'text-slate-400' }}">
                                    {{ number_format($row['downtime'], 1) }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-sky-400">
                                    {{ number_format($row['mttr'], 1) }}
                                </td>
                                <td class="py-3 px-4 text-right font-semibold text-emerald-400">
                                    {{ number_format($row['mtbf'], 1) }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold {{ $row['availability'] >= 95 ? 'text-emerald-400' : ($row['availability'] >= 85 ? 'text-amber-400' : 'text-rose-400') }}">
                                    {{ number_format($row['availability'], 1) }}%
                                </td>
                                <td class="py-3 px-4 text-right text-slate-300">
                                    {{ number_format($row['avg_response'], 1) }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if ($row['status_color'] === 'green')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            <span>Optimal (>95%)</span>
                                        </span>
                                    @elseif ($row['status_color'] === 'amber')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                            <span>Perlu Perhatian (85-95%)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-500/15 text-rose-400 border border-rose-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-400"></span>
                                            <span>Kritis (<85%)</span>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr id="emptyTableRow">
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="w-10 h-10 text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <p class="font-semibold text-slate-300">Tidak ada riwayat breakdown untuk scope mesin dan periode ini.</p>
                                        <p class="text-xs text-slate-500 mt-1">Silakan sesuaikan filter PIC, Mesin, atau ubah rentang tanggal.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        // Global variables data dari backend
        const chartDataDowntime = @json($chartTopDowntime);
        const chartDataFrequency = @json($chartTopFrequency);
        const chartDataTrend = @json($chartTrend);
        const chartDataComponents = @json($chartTopComponents);

        // Chart instances
        let chartInstanceDowntime = null;
        let chartInstanceFrequency = null;
        let chartInstanceTrend = null;
        let chartInstanceComponents = null;

        function submitFilter() {
            document.getElementById('analyticsFilterForm').submit();
        }

        function submitMonthFilter() {
            document.getElementById('filterPeriod').value = 'month';
            document.getElementById('analyticsFilterForm').submit();
        }

        function selectQuickPeriod(period) {
            document.getElementById('filterPeriod').value = period;
            document.getElementById('analyticsFilterForm').submit();
        }

        function applyCustomDate() {
            document.getElementById('filterPeriod').value = 'custom';
            document.getElementById('analyticsFilterForm').submit();
        }

        // Search pada tabel
        const searchInput = document.getElementById('tableSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const keyword = this.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.machine-row');
                let visibleCount = 0;

                rows.forEach(row => {
                    const code = row.querySelector('.machine-code')?.textContent.toLowerCase() || '';
                    if (code.includes(keyword)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        // Helper warna tema
        function getThemeColors(isDark) {
            return {
                gridColor: isDark ? 'rgba(255, 255, 255, 0.08)' : '#e2e8f0',
                textColor: isDark ? '#94a3b8' : '#475569',
                legendColor: isDark ? '#cbd5e1' : '#334155',
                tooltipBg: isDark ? '#181A2F' : '#ffffff',
                tooltipTitle: isDark ? '#ffffff' : '#0f172a',
                tooltipBody: isDark ? '#cbd5e1' : '#334155',
                tooltipBorder: isDark ? '#37415C' : '#cbd5e1',
            };
        }

        // Inisialisasi atau re-render seluruh 4 chart
        function renderAllCharts(isDark) {
            const colors = getThemeColors(isDark);

            // Destroy existing instances
            if (chartInstanceDowntime) chartInstanceDowntime.destroy();
            if (chartInstanceFrequency) chartInstanceFrequency.destroy();
            if (chartInstanceTrend) chartInstanceTrend.destroy();
            if (chartInstanceComponents) chartInstanceComponents.destroy();

            // 1. Chart Top 10 Mesin Breakdown Terlama
            const ctxDowntime = document.getElementById('topDowntimeChart')?.getContext('2d');
            if (ctxDowntime) {
                chartInstanceDowntime = new Chart(ctxDowntime, {
                    type: 'bar',
                    data: {
                        labels: chartDataDowntime.labels.length > 0 ? chartDataDowntime.labels : ['Tidak Ada Data'],
                        datasets: [{
                            label: 'Downtime (Jam)',
                            data: chartDataDowntime.data.length > 0 ? chartDataDowntime.data : [0],
                            backgroundColor: isDark ? '#B4182D' : '#e11d48',
                            borderColor: isDark ? '#FDA481' : '#fb7185',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: colors.tooltipBg,
                                titleColor: colors.tooltipTitle,
                                bodyColor: colors.tooltipBody,
                                borderColor: colors.tooltipBorder,
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: (ctx) => ` Total Downtime: ${ctx.parsed.y} Jam`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 }, maxRotation: 35, minRotation: 0 }
                            },
                            y: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 } },
                                title: { display: true, text: 'Jam', color: colors.textColor, font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // 2. Chart Top 10 Mesin Paling Sering Rusak
            const ctxFrequency = document.getElementById('topFrequencyChart')?.getContext('2d');
            if (ctxFrequency) {
                chartInstanceFrequency = new Chart(ctxFrequency, {
                    type: 'bar',
                    data: {
                        labels: chartDataFrequency.labels.length > 0 ? chartDataFrequency.labels : ['Tidak Ada Data'],
                        datasets: [{
                            label: 'Jumlah Kerusakan (WO)',
                            data: chartDataFrequency.data.length > 0 ? chartDataFrequency.data : [0],
                            backgroundColor: isDark ? '#10b981' : '#059669',
                            borderColor: isDark ? '#34d399' : '#10b981',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: colors.tooltipBg,
                                titleColor: colors.tooltipTitle,
                                bodyColor: colors.tooltipBody,
                                borderColor: colors.tooltipBorder,
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: (ctx) => ` Frekuensi: ${ctx.parsed.y} Kali`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 }, maxRotation: 35, minRotation: 0 }
                            },
                            y: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 }, stepSize: 1 },
                                title: { display: true, text: 'Kejadian', color: colors.textColor, font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // 3. Chart Tren Bulanan Downtime & MTTR (Combo Chart)
            const ctxTrend = document.getElementById('trendChart')?.getContext('2d');
            if (ctxTrend) {
                chartInstanceTrend = new Chart(ctxTrend, {
                    data: {
                        labels: chartDataTrend.labels,
                        datasets: [
                            {
                                type: 'bar',
                                label: 'Total Downtime (Jam)',
                                data: chartDataTrend.downtimes,
                                backgroundColor: isDark ? 'rgba(253, 164, 129, 0.8)' : 'rgba(249, 115, 22, 0.8)',
                                borderColor: isDark ? '#FDA481' : '#f97316',
                                borderWidth: 1,
                                borderRadius: 6,
                                yAxisID: 'yDowntime',
                            },
                            {
                                type: 'line',
                                label: 'MTTR (Jam/Kejadian)',
                                data: chartDataTrend.mttr,
                                borderColor: '#38bdf8',
                                backgroundColor: '#38bdf8',
                                borderWidth: 2.5,
                                pointBackgroundColor: '#38bdf8',
                                pointRadius: 4,
                                tension: 0.25,
                                yAxisID: 'yMttr',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                labels: { color: colors.legendColor, font: { size: 11 } }
                            },
                            tooltip: {
                                backgroundColor: colors.tooltipBg,
                                titleColor: colors.tooltipTitle,
                                bodyColor: colors.tooltipBody,
                                borderColor: colors.tooltipBorder,
                                borderWidth: 1,
                                padding: 10,
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 } }
                            },
                            yDowntime: {
                                type: 'linear',
                                position: 'left',
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 } },
                                title: { display: true, text: 'Downtime (Jam)', color: isDark ? '#FDA481' : '#f97316', font: { size: 10 } }
                            },
                            yMttr: {
                                type: 'linear',
                                position: 'right',
                                grid: { display: false },
                                ticks: { color: '#38bdf8', font: { size: 10 } },
                                title: { display: true, text: 'MTTR (Jam)', color: '#38bdf8', font: { size: 10 } }
                            }
                        }
                    }
                });
            }

            // 4. Chart Top Komponen / Object Part (Horizontal Bar)
            const ctxComponents = document.getElementById('topComponentsChart')?.getContext('2d');
            if (ctxComponents) {
                chartInstanceComponents = new Chart(ctxComponents, {
                    type: 'bar',
                    data: {
                        labels: chartDataComponents.labels.length > 0 ? chartDataComponents.labels : ['Tidak Ada Data'],
                        datasets: [{
                            label: 'Frekuensi Kerusakan',
                            data: chartDataComponents.frequencies.length > 0 ? chartDataComponents.frequencies : [0],
                            backgroundColor: isDark ? '#8b5cf6' : '#7c3aed',
                            borderColor: isDark ? '#a78bfa' : '#8b5cf6',
                            borderWidth: 1,
                            borderRadius: 6,
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: colors.tooltipBg,
                                titleColor: colors.tooltipTitle,
                                bodyColor: colors.tooltipBody,
                                borderColor: colors.tooltipBorder,
                                borderWidth: 1,
                                padding: 10,
                                callbacks: {
                                    label: function(ctx) {
                                        const idx = ctx.dataIndex;
                                        const freq = ctx.parsed.x;
                                        const dt = chartDataComponents.downtimes[idx] || 0;
                                        return ` Frekuensi: ${freq} Kali | Downtime: ${dt} Jam`;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 }, stepSize: 1 },
                                title: { display: true, text: 'Frekuensi (Kali)', color: colors.textColor, font: { size: 10 } }
                            },
                            y: {
                                grid: { color: colors.gridColor },
                                ticks: { color: colors.textColor, font: { size: 10 } }
                            }
                        }
                    }
                });
            }
        }

        // Render pertama saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const isDark = document.documentElement.classList.contains('dark');
            renderAllCharts(isDark);
        });

        // Listener re-render otomatis seketika saat tema beralih (Light / Dark)
        window.addEventListener('theme-changed', function(e) {
            const isDark = e.detail ? e.detail.isDark : document.documentElement.classList.contains('dark');
            renderAllCharts(isDark);
        });
    </script>
</x-app-layout>
