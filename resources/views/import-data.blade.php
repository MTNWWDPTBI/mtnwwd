<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Import Master Data (BOM & Suku Cadang)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Unggah berkas Excel (.xlsx / .xls) untuk sinkronisasi otomatis mesin, assembly, sparepart, dan relasi BOM.
            </p>
        </div>

        @if (session('success'))
            <div class="bg-[#242E49] border border-emerald-500/40 text-emerald-300 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-6 h-6 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-[#242E49] border border-rose-500/40 text-rose-300 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-6 h-6 text-rose-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 p-6 sm:p-8">
            <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2" for="file_excel">
                        Pilih Berkas Excel (.xlsx, .xls)
                    </label>
                    <div class="relative border-2 border-dashed border-[#37415C] hover:border-[#FDA481]/60 rounded-2xl p-6 text-center bg-[#181A2F]/80 transition">
                        <svg class="w-10 h-10 mx-auto text-[#FDA481] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <input type="file" 
                               name="file_excel" 
                               id="file_excel" 
                               accept=".xlsx, .xls" 
                               class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FDA481]/20 file:text-[#FDA481] hover:file:bg-[#FDA481]/30 cursor-pointer" 
                               required>
                        <p class="text-[11px] text-slate-500 mt-2">Mendukung format multi-sheet: Machine_Master, Assembly_Master, Sparepart_Master, Assembly_BOM.</p>
                    </div>
                    @error('file_excel')
                        <p class="text-rose-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#37415C]/50">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#181A2F] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                        Upload & Proses Data
                    </button>
                </div>
            </form>
        </div>
    
                <!-- KOTAK IMPORT DATA SAP -->
        <div class="bg-[#242E49] border border-[#37415C] rounded-2xl p-6 mt-6 shadow-m3-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <span>📊</span>
                    <span>Import History SAP</span>
                </h2>
                @if (($sapStats['total_rows'] ?? 0) > 0)
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 w-fit">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Database Aktif ({{ number_format($sapStats['total_rows'], 0, ',', '.') }} Baris)
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/30 w-fit">
                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                        Database Kosong
                    </span>
                @endif
            </div>
            
            <p class="text-sm text-slate-400 mb-4">
                Unggah file hasil export SAP yang sudah disimpan dalam format <b>.CSV (Comma Delimited)</b>.
            </p>

            <!-- Indikator: Status Data & Riwayat Upload Terakhir -->
            <div class="mb-5 p-4 rounded-xl bg-[#181A2F]/80 border border-[#37415C]/60">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-300 mb-3 flex items-center gap-2">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Status Data & Riwayat Upload Terakhir</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Metrik 1: Total Baris Data -->
                    <div class="bg-[#242E49] border border-[#37415C]/60 rounded-xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 border border-blue-500/20 flex items-center justify-center text-blue-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1.5 3 3.5 3h9c2 0 3.5-1 3.5-3V7M4 7c0-2 1.5-3 3.5-3h9c2 0 3.5 1 3.5 3M4 7h16m-16 5h16m-16 5h16"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-slate-400">Total Baris Data</p>
                            <p class="text-base font-extrabold text-white truncate">
                                {{ number_format($sapStats['total_rows'] ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">Baris</span>
                            </p>
                        </div>
                    </div>

                    <!-- Metrik 2: Transaksi SAP Terakhir -->
                    <div class="bg-[#242E49] border border-[#37415C]/60 rounded-xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-slate-400">Transaksi SAP Terakhir</p>
                            <p class="text-sm font-extrabold text-white truncate">
                                @if (!empty($sapStats['latest_date']))
                                    {{ \Carbon\Carbon::parse($sapStats['latest_date'])->translatedFormat('d M Y') }}
                                @else
                                    <span class="text-slate-500 text-xs font-normal">Belum ada data</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Metrik 3: Waktu Upload Terakhir -->
                    <div class="bg-[#242E49] border border-[#37415C]/60 rounded-xl p-3.5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium text-slate-400">Waktu Upload Terakhir</p>
                            <p class="text-xs font-bold text-white truncate">
                                @if (!empty($sapStats['last_uploaded_at']))
                                    {{ \Carbon\Carbon::parse($sapStats['last_uploaded_at'])->format('d M Y, H:i') }}
                                    <span class="block text-[10px] text-slate-400 font-normal">
                                        ({{ \Carbon\Carbon::parse($sapStats['last_uploaded_at'])->diffForHumans() }})
                                    </span>
                                @else
                                    <span class="text-slate-500 text-xs font-normal">Belum pernah</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Upload File CSV -->
            <form action="{{ route('import.sap') }}" method="POST" enctype="multipart/form-data" class="flex flex-col md:flex-row gap-4 items-center">
                @csrf
                <div class="w-full">
                    <input type="file" name="file" accept=".csv" required
                        class="block w-full text-sm text-slate-400
                        file:mr-4 file:py-2.5 file:px-4
                        file:rounded-xl file:border-0
                        file:text-sm file:font-semibold
                        file:bg-[#181A2F] file:text-white
                        hover:file:bg-[#37415C] file:cursor-pointer file:transition
                        border border-[#37415C] rounded-xl bg-[#181A2F]">
                </div>
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-500 text-white text-sm font-bold shadow-md transition duration-200 shrink-0">
                    Mulai Import SAP
                </button>
            </form>
        </div>
    
    </div>
</x-app-layout>