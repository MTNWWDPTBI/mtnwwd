<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="font-bold text-xl text-white tracking-wide flex items-center gap-2">
                    <span class="text-[#00a884]">📊</span>
                    <span>Dashboard Laporan PIC: {{ auth()->user()->name }}</span>
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Analisis data kerusakan bulanan & generator materi presentasi PowerPoint (PPT)
                </p>
            </div>
            
            @if (auth()->user()?->role === 'admin' || (bool) (auth()->user()?->is_admin ?? false))
                <div class="flex items-center gap-3">
                    <button type="button" 
                            onclick="openImportModal()" 
                            class="inline-flex items-center gap-2 px-4 py-2 bg-[#202c33] hover:bg-[#2a3942] text-[#00a884] border border-[#00a884]/40 hover:border-[#00a884] rounded-xl text-sm font-semibold transition duration-200 shadow-lg">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        <span>Import Data PIC (Excel)</span>
                    </button>
                </div>
            @endif
        </div>
    </x-slot>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-2 sm:px-4 mb-4">
        @if (session('success'))
            <div class="p-4 mb-3 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center gap-3 shadow-md">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 mb-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center gap-3 shadow-md">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 mb-3 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm shadow-md">
                <p class="font-bold mb-1">Perhatian:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    <!-- Main Content Container -->
    <div class="max-w-7xl mx-auto px-2 sm:px-4 space-y-6">

        <!-- Form Filter Card -->
        <div class="bg-[#202c33] border border-[#222e35] rounded-2xl p-5 shadow-xl">
            <form id="filterForm" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                <!-- Dropdown Select Mesin PIC (Multi-Select: 1, Beberapa, atau Semua) -->
                <div class="md:col-span-5 relative" id="multiSelectContainer">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300">
                            Pilih Mesin PIC
                        </label>
                        <span class="text-[11px] text-slate-400">
                            (Bisa 1, beberapa, atau semua)
                        </span>
                    </div>

                    <!-- Trigger Button Dropdown -->
                    <button type="button" 
                            id="btnToggleMachineDropdown"
                            onclick="toggleMachineDropdown()"
                            class="w-full bg-[#111b21] border border-[#222e35] hover:border-[#00a884] focus:border-[#00a884] rounded-xl px-4 py-2.5 text-sm text-left flex items-center justify-between transition shadow-inner group">
                        <div class="flex items-center gap-2.5 truncate min-w-0 pr-2">
                            <span class="text-base text-[#00a884]">⚙️</span>
                            <span id="selectedMachinesLabel" class="text-slate-100 font-medium truncate">
                                Semua Mesin Saya ({{ count($machines) }} Mesin)
                            </span>
                        </div>
                        
                        <div class="flex items-center gap-2 shrink-0">
                            <span id="selectedCountBadge" class="bg-[#00a884]/20 text-[#00a884] text-xs px-2 py-0.5 rounded-full font-semibold border border-[#00a884]/30">
                                Semua ({{ count($machines) }})
                            </span>
                            <svg id="machineDropdownChevron" class="w-4 h-4 text-slate-400 group-hover:text-white transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>

                    <!-- Dropdown Popover Panel -->
                    <div id="machineDropdownPanel" 
                         class="hidden absolute left-0 right-0 top-full mt-2 z-50 bg-[#182229] border border-[#2a3942] rounded-2xl shadow-2xl p-3 space-y-2.5 backdrop-blur-md">
                        
                        <!-- Search Bar -->
                        <div class="relative">
                            <input type="text" 
                                   id="machineSearchInput" 
                                   placeholder="Cari kode mesin (contoh: 4001)..." 
                                   class="w-full bg-[#111b21] border border-[#2a3942] focus:border-[#00a884] focus:ring-1 focus:ring-[#00a884] rounded-xl px-3 py-1.5 pl-8 text-xs text-slate-200 placeholder-slate-500 transition">
                            <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Quick Action Buttons -->
                        <div class="flex items-center justify-between px-1 text-xs">
                            <button type="button" 
                                    onclick="selectAllMachines()" 
                                    class="text-[#00a884] hover:underline font-semibold flex items-center gap-1">
                                ✓ Pilih Semua
                            </button>
                            <button type="button" 
                                    onclick="deselectAllMachines()" 
                                    class="text-rose-400 hover:underline font-semibold flex items-center gap-1">
                                ✕ Hapus Pilihan
                            </button>
                        </div>

                        <!-- Checkbox: Pilih Semua Mesin -->
                        <div class="pt-1 border-t border-[#222e35]">
                            <label class="flex items-center gap-3 px-2.5 py-1.5 rounded-xl hover:bg-[#202c33] cursor-pointer transition">
                                <input type="checkbox" 
                                       id="selectAllCheckbox" 
                                       checked
                                       onchange="this.checked ? selectAllMachines() : deselectAllMachines()"
                                       class="w-4 h-4 rounded bg-[#111b21] border-[#2a3942] text-[#00a884] focus:ring-[#00a884] focus:ring-offset-0 cursor-pointer">
                                <span class="text-xs font-bold text-white">Semua Mesin PIC Saya</span>
                                <span class="text-[10px] text-slate-400 bg-[#111b21] px-1.5 py-0.5 rounded border border-[#222e35] ml-auto">
                                    {{ count($machines) }} Mesin
                                </span>
                            </label>
                        </div>

                        <!-- Scrollable Machine Checkbox List -->
                        <div id="machineItemsContainer" class="max-h-56 overflow-y-auto custom-scrollbar space-y-0.5 pr-1 border-t border-[#222e35] pt-1">
                            @forelse ($machines as $machine)
                                <label class="flex items-center gap-3 px-2.5 py-1.5 rounded-xl hover:bg-[#202c33] cursor-pointer transition machine-item" data-code="{{ $machine }}">
                                    <input type="checkbox" 
                                           name="machine_cb" 
                                           value="{{ $machine }}" 
                                           checked
                                           onchange="updateSelectionState()"
                                           class="machine-checkbox w-4 h-4 rounded bg-[#111b21] border-[#2a3942] text-[#00a884] focus:ring-[#00a884] focus:ring-offset-0 cursor-pointer">
                                    <span class="text-xs font-mono font-medium text-slate-200 machine-code">{{ $machine }}</span>
                                    <span class="text-[10px] text-[#00a884] bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20 ml-auto font-semibold">
                                        PIC
                                    </span>
                                </label>
                            @empty
                                <div class="p-3 text-center text-xs text-slate-400">
                                    Belum ada mesin yang ditugaskan ke akun Anda.
                                </div>
                            @endforelse
                        </div>

                        <!-- Dropdown Footer -->
                        <div class="pt-2 border-t border-[#222e35] flex items-center justify-between text-xs">
                            <span id="selectionCounterFooter" class="text-[11px] text-slate-400 font-medium">
                                {{ count($machines) }} dari {{ count($machines) }} mesin dipilih
                            </span>
                            <button type="button" 
                                    onclick="toggleMachineDropdown(false)" 
                                    class="px-3 py-1 bg-[#00a884] hover:bg-[#008f6f] text-white rounded-lg text-xs font-semibold transition">
                                Selesai
                            </button>
                        </div>

                    </div>
                </div>

                <!-- Input Type Month -->
                <div class="md:col-span-4">
                    <label for="filterMonth" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                        Periode Bulan
                    </label>
                    <input type="month" 
                           id="filterMonth" 
                           name="month" 
                           value="{{ date('Y-m') }}" 
                           class="w-full bg-[#111b21] border border-[#222e35] focus:border-[#00a884] focus:ring-1 focus:ring-[#00a884] rounded-xl px-4 py-2 text-sm text-slate-100 transition [color-scheme:dark]">
                </div>

                <!-- Action Button -->
                <div class="md:col-span-3">
                    <button type="submit" 
                            id="btnFilterSubmit" 
                            class="w-full flex items-center justify-center gap-2 px-5 py-2.5 bg-[#00a884] hover:bg-[#008f6f] text-white font-semibold text-sm rounded-xl transition duration-200 shadow-lg shadow-[#00a884]/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <span>Tampilkan Data</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Section Dashboard: Grid 2 Kolom -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- Kolom Kiri: Card Bar Chart Top 5 Komponen Rusak -->
            <div class="bg-[#202c33] border border-[#222e35] rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                <div>
                    <!-- Header Card Grafik -->
                    <div class="flex items-center justify-between pb-4 border-b border-[#222e35] mb-4">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-[#00a884]"></span>
                                <span>Top 5 Komponen Sering Rusak</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Berdasarkan frekuensi kejadian & total jam downtime
                            </p>
                        </div>
                        
                        <button type="button" 
                                id="btnCopyChart" 
                                onclick="copyChartToClipboard()"
                                title="Salin gambar grafik ke clipboard untuk di-paste langsung (Ctrl+V) ke PowerPoint"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#111b21] hover:bg-[#182229] text-[#00a884] hover:text-white border border-[#00a884]/40 hover:border-[#00a884] rounded-lg text-xs font-medium transition shadow">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                            </svg>
                            <span>Salin Grafik</span>
                        </button>
                    </div>

                    <!-- Canvas Chart.js Container -->
                    <div class="relative w-full h-[320px] bg-[#111b21] border border-[#222e35] rounded-xl p-3 flex items-center justify-center">
                        <canvas id="chartTopParts"></canvas>
                        
                        <!-- Empty State Overlay -->
                        <div id="chartEmptyState" class="hidden absolute inset-0 bg-[#111b21] flex flex-col items-center justify-center text-slate-400 p-6 text-center rounded-xl">
                            <svg class="w-12 h-12 text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-semibold text-slate-300">Tidak Ada Data Kerusakan</p>
                            <p class="text-xs text-slate-500 mt-1" id="chartEmptyMessage">
                                Tidak ditemukan histori kerusakan pada periode ini.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Footer Metrik Angka -->
                <div class="mt-4 pt-4 border-t border-[#222e35] grid grid-cols-2 sm:grid-cols-3 gap-3 text-center">
                    <div class="bg-[#111b21] p-2.5 rounded-xl border border-[#222e35]">
                        <span class="block text-[11px] text-slate-400 uppercase font-medium">Total Kejadian</span>
                        <span id="metricEvents" class="text-lg font-bold text-[#00a884]">-</span>
                    </div>
                    <div class="bg-[#111b21] p-2.5 rounded-xl border border-[#222e35]">
                        <span class="block text-[11px] text-slate-400 uppercase font-medium">Total Downtime</span>
                        <span id="metricDowntime" class="text-lg font-bold text-[#fda481]">-</span>
                    </div>
                    <div class="bg-[#111b21] p-2.5 rounded-xl border border-[#222e35] col-span-2 sm:col-span-1">
                        <span class="block text-[11px] text-slate-400 uppercase font-medium">Mesin Terfilter</span>
                        <span id="metricMachines" class="text-lg font-bold text-white">-</span>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Card AI Summary Problem Solving PPT -->
            <div class="bg-[#202c33] border border-[#222e35] rounded-2xl p-5 shadow-xl flex flex-col justify-between">
                <div>
                    <!-- Header Card AI Summary -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-4 border-b border-[#222e35] mb-4">
                        <div>
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span class="text-lg">🤖</span>
                                <span>AI Summary Problem Solving PPT</span>
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                Rangkuman otomatis 3 poin utama untuk slide PowerPoint
                            </p>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    id="btnGenerateAi" 
                                    onclick="generateAiSummary()" 
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 text-white rounded-lg text-xs font-semibold transition shadow-md">
                                <svg class="w-4 h-4 animate-spin hidden" id="iconLoadingAi" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <svg class="w-4 h-4" id="iconSparkleAi" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                <span>Generate Summary PPT</span>
                            </button>

                            <button type="button" 
                                    id="btnCopySummary" 
                                    onclick="copySummaryToClipboard()" 
                                    title="Salin teks rangkuman ke clipboard"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#111b21] hover:bg-[#182229] text-slate-300 hover:text-white border border-[#222e35] hover:border-slate-500 rounded-lg text-xs font-medium transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <span>Salin Teks</span>
                            </button>
                        </div>
                    </div>

                    <!-- AI Summary Content Area -->
                    <div id="aiSummaryBox" class="w-full min-h-[320px] max-h-[420px] overflow-y-auto bg-[#111b21] border border-[#222e35] rounded-xl p-4 text-slate-200 text-sm leading-relaxed custom-scrollbar relative">
                        
                        <!-- Initial / Placeholder State -->
                        <div id="aiInitialState" class="flex flex-col items-center justify-center h-full min-h-[280px] text-center text-slate-400 p-6">
                            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-xl text-emerald-400 mb-3">
                                📑
                            </div>
                            <h4 class="text-sm font-semibold text-slate-200">Materi Slide PPT Belum Digenerate</h4>
                            <p class="text-xs text-slate-400 max-w-sm mt-1">
                                Klik tombol <strong>"Generate Summary PPT"</strong> untuk menyusun analisis otomatis 3 poin (Fokus Masalah, Root Cause, Action Plan & TPM) berdasarkan seluruh riwayat kerusakan pada periode bulan yang dipilih.
                            </p>
                        </div>

                        <!-- Loading State with Bouncing Dots -->
                        <div id="aiLoadingState" class="hidden flex flex-col items-center justify-center h-full min-h-[280px] text-center p-6 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                                <span class="typing-dot"></span>
                            </div>
                            <p class="text-xs font-semibold text-[#00a884] animate-pulse">
                                AI sedang menganalisis seluruh riwayat kerusakan pada bulan terpilih...
                            </p>
                            <p class="text-[11px] text-slate-500">
                                Menyusun fokus masalah terbanyak, analisis akar masalah, dan action plan & TPM preventif
                            </p>
                        </div>

                        <!-- Result Container -->
                        <div id="aiResultText" class="hidden prose prose-invert max-w-none text-xs sm:text-sm ai-content"></div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-[#222e35] flex items-center justify-between text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-[#00a884]"></span>
                        Diperkuat oleh MTN-AI Bekaert Engine
                    </span>
                    <span id="aiTimestamp" class="text-slate-400">Siap</span>
                </div>
            </div>

        </div>

    </div>

    @if (auth()->user()?->role === 'admin' || (bool) (auth()->user()?->is_admin ?? false))
        <!-- Modal Form Upload Excel PIC -->
        <div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-black/80 backdrop-blur-sm transition-opacity">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="w-full max-w-md bg-[#202c33] border border-[#222e35] rounded-2xl shadow-2xl p-6 relative">
                    
                    <!-- Close Button -->
                    <button type="button" 
                            onclick="closeImportModal()" 
                            class="absolute top-4 right-4 text-slate-400 hover:text-white p-1 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <!-- Modal Header -->
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 font-bold">
                            📥
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-white">Import Pemetaan Mesin PIC</h3>
                            <p class="text-xs text-slate-400">Unggah file Excel pemetaan mesin teknisi</p>
                        </div>
                    </div>

                    <!-- Petunjuk Format Header -->
                    <div class="mb-4 p-3 bg-[#111b21] border border-[#222e35] rounded-xl text-xs text-slate-300">
                        <p class="font-semibold text-[#00a884] mb-1">Format Header Kolom Excel:</p>
                        <p class="text-slate-400 mb-2">Pastikan baris pertama (header) memuat kolom berikut:</p>
                        <div class="grid grid-cols-2 gap-2 text-[11px] font-mono">
                            <span class="bg-[#182229] p-1.5 rounded border border-[#222e35] text-slate-200">email (atau nik)</span>
                            <span class="bg-[#182229] p-1.5 rounded border border-[#222e35] text-slate-200">functional_location</span>
                        </div>
                    </div>

                    <!-- Form Upload -->
                    <form action="{{ route('pic.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Pilih File (.xlsx, .csv, .xls)</label>
                            <input type="file" 
                                   name="file" 
                                   required 
                                   accept=".xlsx,.csv,.xls"
                                   class="w-full text-xs text-slate-300 bg-[#111b21] border border-[#222e35] rounded-xl cursor-pointer file:mr-3 file:py-2.5 file:px-4 file:rounded-l-xl file:border-0 file:text-xs file:font-semibold file:bg-[#00a884] file:text-white hover:file:bg-[#008f6f]">
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" 
                                    onclick="closeImportModal()" 
                                    class="px-4 py-2 bg-[#111b21] hover:bg-[#182229] text-slate-300 border border-[#222e35] rounded-xl text-xs font-medium transition">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-[#00a884] hover:bg-[#008f6f] text-white rounded-xl text-xs font-semibold transition shadow-md shadow-[#00a884]/20">
                                Unggah & Proses
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    @endif

    <!-- Floating Toast Notification -->
    <div id="toastNotification" class="fixed bottom-6 right-6 z-50 hidden bg-[#202c33] border border-[#00a884] text-white px-4 py-3 rounded-xl shadow-2xl flex items-center gap-3 transition-all duration-300 transform translate-y-2">
        <span class="text-base text-[#00a884]">✅</span>
        <span id="toastMessage" class="text-xs font-medium"></span>
    </div>

    <!-- Scripts: CDN jQuery, Chart.js, Marked.js -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

    <style>
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(255, 255, 255, 0.15); border-radius: 9999px; }

        /* Bouncing Dots Animation */
        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #00a884;
            border-radius: 50%;
            display: inline-block;
            animation: typingBounce 1.4s infinite ease-in-out both;
        }
        .typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes typingBounce {
            0%, 80%, 100% { transform: scale(0); opacity: 0.3; }
            40% { transform: scale(1); opacity: 1; }
        }

        /* Typography inside AI Markdown container */
        .ai-content h1, .ai-content h2, .ai-content h3, .ai-content h4 {
            color: #ffffff;
            font-weight: 700;
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
        }
        .ai-content h3 { font-size: 1rem; color: #00a884; }
        .ai-content strong { color: #f8fafc; }
        .ai-content ul { list-style-type: disc; padding-left: 1.25rem; margin: 0.5rem 0; }
        .ai-content ol { list-style-type: decimal; padding-left: 1.25rem; margin: 0.5rem 0; }
        .ai-content li { margin-bottom: 0.25rem; }
        .ai-content p { margin-bottom: 0.6rem; }
        .ai-content p:last-child { margin-bottom: 0; }
    </style>

    <script>
        let myChart = null;
        let lastAiSummaryText = '';
        const totalMachinesCount = {{ count($machines) }};

        // Toast Helper
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const msgSpan = document.getElementById('toastMessage');
            msgSpan.textContent = message;
            toast.classList.remove('hidden', 'translate-y-2');
            
            setTimeout(() => {
                toast.classList.add('translate-y-2');
                setTimeout(() => toast.classList.add('hidden'), 200);
            }, 3500);
        }

        // Modal Helpers
        function openImportModal() {
            document.getElementById('importModal').classList.remove('hidden');
        }
        function closeImportModal() {
            document.getElementById('importModal').classList.add('hidden');
        }

        // ==========================================
        // Multi-Select Dropdown Logics
        // ==========================================
        function toggleMachineDropdown(forceState = null) {
            const panel = $('#machineDropdownPanel');
            const chevron = $('#machineDropdownChevron');
            const shouldOpen = forceState !== null ? forceState : panel.hasClass('hidden');

            if (shouldOpen) {
                panel.removeClass('hidden');
                chevron.addClass('rotate-180');
                $('#machineSearchInput').focus();
            } else {
                panel.addClass('hidden');
                chevron.removeClass('rotate-180');
            }
        }

        // Tutup dropdown jika klik di luar area
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#multiSelectContainer').length) {
                toggleMachineDropdown(false);
            }
        });

        // Filter pencarian kode mesin di dalam dropdown
        $(document).on('input', '#machineSearchInput', function() {
            const q = $(this).val().toLowerCase().trim();
            $('.machine-item').each(function() {
                const code = ($(this).data('code') || '').toString().toLowerCase();
                if (code.includes(q)) {
                    $(this).removeClass('hidden');
                } else {
                    $(this).addClass('hidden');
                }
            });
        });

        // Update trigger label & badge berdasarkan checkbox yang dicentang
        function updateSelectionState() {
            const checkedBoxes = $('.machine-checkbox:checked');
            const count = checkedBoxes.length;
            const labelSpan = $('#selectedMachinesLabel');
            const badgeSpan = $('#selectedCountBadge');
            const selectAllCb = $('#selectAllCheckbox');

            if (count === 0 || count === totalMachinesCount) {
                selectAllCb.prop('checked', count === totalMachinesCount);
                selectAllCb.prop('indeterminate', false);
                labelSpan.text('Semua Mesin Saya (' + totalMachinesCount + ' Mesin)');
                badgeSpan.text('Semua (' + totalMachinesCount + ')')
                         .removeClass('bg-sky-500/20 text-sky-400 border-sky-500/30')
                         .addClass('bg-[#00a884]/20 text-[#00a884] border-[#00a884]/30');
            } else if (count === 1) {
                selectAllCb.prop('checked', false);
                selectAllCb.prop('indeterminate', false);
                const singleVal = checkedBoxes.first().val();
                labelSpan.text(singleVal);
                badgeSpan.text('1 Mesin')
                         .removeClass('bg-[#00a884]/20 text-[#00a884] border-[#00a884]/30')
                         .addClass('bg-sky-500/20 text-sky-400 border-sky-500/30');
            } else {
                selectAllCb.prop('checked', false);
                selectAllCb.prop('indeterminate', true);
                
                const codes = [];
                checkedBoxes.each(function(i) {
                    if (i < 2) {
                        const raw = $(this).val();
                        const parts = raw.split('-');
                        codes.push(parts[parts.length - 1] || raw);
                    }
                });
                const remaining = count - codes.length;
                const summary = count + ' Mesin (' + codes.join(', ') + (remaining > 0 ? ' +' + remaining : '') + ')';
                labelSpan.text(summary);
                badgeSpan.text(count + ' Dipilih')
                         .removeClass('bg-[#00a884]/20 text-[#00a884] border-[#00a884]/30')
                         .addClass('bg-sky-500/20 text-sky-400 border-sky-500/30');
            }

            $('#selectionCounterFooter').text(count + ' dari ' + totalMachinesCount + ' mesin dipilih');
        }

        // Pilih Semua Mesin
        function selectAllMachines() {
            $('.machine-checkbox').prop('checked', true);
            $('#selectAllCheckbox').prop('checked', true).prop('indeterminate', false);
            updateSelectionState();
        }

        // Hapus Semua Pilihan
        function deselectAllMachines() {
            $('.machine-checkbox').prop('checked', false);
            $('#selectAllCheckbox').prop('checked', false).prop('indeterminate', false);
            updateSelectionState();
        }

        // Ambil array mesin yang dipilih untuk AJAX
        function getSelectedMachines() {
            const checkedBoxes = $('.machine-checkbox:checked');
            const count = checkedBoxes.length;

            // Jika semua dipilih atau tidak ada yang dipilih, kembalikan array kosong (backend mengartikan seluruh mesin PIC user)
            if (count === 0 || count === totalMachinesCount) {
                return [];
            }

            const selected = [];
            checkedBoxes.each(function() {
                selected.push($(this).val());
            });
            return selected;
        }

        // 1. AJAX: Fetch Data & Render Chart.js
        function loadReportData() {
            const machines = getSelectedMachines();
            const month = $('#filterMonth').val();

            if (!month) {
                alert('Silakan pilih periode bulan.');
                return;
            }

            // Disable submit button during request
            $('#btnFilterSubmit').prop('disabled', true).addClass('opacity-75');

            $.ajax({
                url: "{{ route('pic.generate') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    machines: machines,
                    month: month
                },
                success: function(res) {
                    $('#btnFilterSubmit').prop('disabled', false).removeClass('opacity-75');

                    if (res.status === 'success' && res.labels && res.labels.length > 0) {
                        $('#chartEmptyState').addClass('hidden');
                        renderChart(res.labels, res.frequencies, res.downtimes);

                        $('#metricEvents').text((res.total_events || 0) + ' kali');
                        $('#metricDowntime').text((res.total_downtime || 0) + ' Jam');
                        $('#metricMachines').text((res.machine_count || 1) + ' Unit');
                    } else {
                        // Empty state
                        if (myChart) {
                            myChart.destroy();
                            myChart = null;
                        }
                        $('#chartEmptyMessage').text(res.message || 'Tidak ada catatan kerusakan pada mesin dan bulan yang dipilih.');
                        $('#chartEmptyState').removeClass('hidden');

                        $('#metricEvents').text('0 kali');
                        $('#metricDowntime').text('0 Jam');
                        $('#metricMachines').text((res.machine_count || 0) + ' Unit');
                    }
                },
                error: function(xhr) {
                    $('#btnFilterSubmit').prop('disabled', false).removeClass('opacity-75');
                    const err = xhr.responseJSON?.message || 'Gagal memuat data laporan.';
                    alert('Error: ' + err);
                }
            });
        }

        // Render Chart.js
        function renderChart(labels, frequencies, downtimes) {
            const ctx = document.getElementById('chartTopParts').getContext('2d');

            if (myChart) {
                myChart.destroy();
            }

            myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Frekuensi Kerusakan (Kali)',
                            data: frequencies,
                            backgroundColor: '#00a884',
                            borderColor: '#00a884',
                            borderRadius: 6,
                            borderWidth: 1,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Total Downtime (Jam)',
                            data: downtimes,
                            backgroundColor: '#fda481',
                            borderColor: '#fda481',
                            borderRadius: 6,
                            borderWidth: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            labels: {
                                color: '#cbd5e1',
                                font: { size: 11, family: 'Figtree, sans-serif' }
                            }
                        },
                        tooltip: {
                            backgroundColor: '#111b21',
                            titleColor: '#f1f5f9',
                            bodyColor: '#cbd5e1',
                            borderColor: '#222e35',
                            borderWidth: 1,
                            padding: 10
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: {
                                color: '#94a3b8',
                                font: { size: 10 },
                                maxRotation: 25,
                                minRotation: 0
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: '#00a884', font: { size: 10 } },
                            title: {
                                display: true,
                                text: 'Frekuensi (Kali)',
                                color: '#00a884',
                                font: { size: 11 }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            ticks: { color: '#fda481', font: { size: 10 } },
                            title: {
                                display: true,
                                text: 'Downtime (Jam)',
                                color: '#fda481',
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        // 2. Salin Grafik ke Clipboard (Direct Image Blob Paste for PowerPoint)
        async function copyChartToClipboard() {
            const canvas = document.getElementById('chartTopParts');
            if (!canvas || !myChart) {
                alert('Tidak ada grafik untuk disalin.');
                return;
            }

            try {
                // Buat canvas sementara dengan background gelap solid agar tidak transparan saat di-paste di PPT
                const tempCanvas = document.createElement('canvas');
                tempCanvas.width = canvas.width;
                tempCanvas.height = canvas.height;
                const tempCtx = tempCanvas.getContext('2d');

                tempCtx.fillStyle = '#111b21';
                tempCtx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
                tempCtx.drawImage(canvas, 0, 0);

                tempCanvas.toBlob(async function(blob) {
                    if (!blob) {
                        alert('Gagal mengekspor grafik ke gambar.');
                        return;
                    }

                    if (navigator.clipboard && navigator.clipboard.write) {
                        try {
                            await navigator.clipboard.write([
                                new ClipboardItem({ 'image/png': blob })
                            ]);
                            showToast('Grafik berhasil disalin ke clipboard! Siap di-paste (Ctrl+V) ke PowerPoint.');
                        } catch (err) {
                            console.error('Clipboard write error:', err);
                            fallbackDownloadCanvas(tempCanvas);
                        }
                    } else {
                        fallbackDownloadCanvas(tempCanvas);
                    }
                }, 'image/png');

            } catch (e) {
                console.error(e);
                alert('Terjadi kendala saat menyalin grafik: ' + e.message);
            }
        }

        // Fallback jika browser membatasi clipboard write gambar
        function fallbackDownloadCanvas(canvas) {
            const a = document.createElement('a');
            a.download = 'grafik-top5-kerusakan-pic.png';
            a.href = canvas.toDataURL('image/png');
            a.click();
            showToast('Browser membatasi clipboard, grafik otomatis diunduh sebagai file PNG.');
        }

        // 3. AJAX: Generate AI Summary Problem Solving PPT
        function generateAiSummary() {
            const machines = getSelectedMachines();
            const month = $('#filterMonth').val();

            if (!month) {
                alert('Silakan pilih periode bulan.');
                return;
            }

            // UI Loading State
            $('#btnGenerateAi').prop('disabled', true).addClass('opacity-75');
            $('#iconSparkleAi').addClass('hidden');
            $('#iconLoadingAi').removeClass('hidden');

            $('#aiInitialState').addClass('hidden');
            $('#aiResultText').addClass('hidden');
            $('#aiLoadingState').removeClass('hidden');
            $('#aiTimestamp').text('Sedang memproses...');

            $.ajax({
                url: "{{ route('pic.ai-summary') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    machines: machines,
                    month: month
                },
                success: function(res) {
                    $('#btnGenerateAi').prop('disabled', false).removeClass('opacity-75');
                    $('#iconLoadingAi').addClass('hidden');
                    $('#iconSparkleAi').removeClass('hidden');

                    $('#aiLoadingState').addClass('hidden');

                    if (res.status === 'success' && res.summary) {
                        lastAiSummaryText = res.summary;

                        // Parse markdown menggunakan marked.js
                        const parsedHtml = marked.parse(res.summary);
                        $('#aiResultText').html(parsedHtml).removeClass('hidden');

                        const now = new Date();
                        const timeStr = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        $('#aiTimestamp').text('Diperbarui pukul ' + timeStr);
                        showToast('AI Summary PPT berhasil digenerate!');
                    } else {
                        $('#aiInitialState').removeClass('hidden');
                        alert(res.message || 'Gagal menghasilkan AI Summary.');
                    }
                },
                error: function(xhr) {
                    $('#btnGenerateAi').prop('disabled', false).removeClass('opacity-75');
                    $('#iconLoadingAi').addClass('hidden');
                    $('#iconSparkleAi').removeClass('hidden');
                    $('#aiLoadingState').addClass('hidden');
                    $('#aiInitialState').removeClass('hidden');

                    const err = xhr.responseJSON?.summary || xhr.responseJSON?.message || 'Koneksi ke AI Gateway terganggu.';
                    alert('Error: ' + err);
                    $('#aiTimestamp').text('Gagal');
                }
            });
        }

        // 4. Salin Teks AI Summary ke Clipboard
        function copySummaryToClipboard() {
            if (!lastAiSummaryText) {
                alert('Belum ada teks AI Summary yang digenerate.');
                return;
            }

            navigator.clipboard.writeText(lastAiSummaryText).then(() => {
                showToast('Teks AI Summary berhasil disalin ke clipboard!');
            }).catch(err => {
                console.error(err);
                alert('Gagal menyalin teks ke clipboard.');
            });
        }

        // Event Listener: Form Filter
        $(document).ready(function() {
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                loadReportData();
            });

            // Otomatis load data pertama kali saat halaman dibuka
            loadReportData();
        });
    </script>
</x-app-layout>
