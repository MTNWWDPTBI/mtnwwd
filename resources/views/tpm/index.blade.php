<x-app-layout>
    <div class="space-y-4 sm:space-y-6 pb-8 sm:pb-12">
        <!-- Header responsif: Di HP menumpuk, di PC sejajar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2 sm:gap-3">
                    <span>Master TPM</span>
                    <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Consumables
                    </span>
                </h1>
                <p class="text-[10px] sm:text-sm text-slate-400 mt-1">
                    Pantau limit stock dan kelola stock TPM per lokasi rak.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                <a href="{{ route('tpm.scan') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-[#181A2F] border border-[#FDA481]/50 hover:bg-[#FDA481]/10 text-[#FDA481] text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <span>📷 Scan</span>
                </a>
                <!-- Sembunyikan dari teknisi -->
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('tpm.create') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-3 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Master Baru</span>
                </a>
                @endif

                @if(auth()->user()->role === 'admin')
                <a href="{{ route('export.tpm', array_filter(['location' => $selectedLocation])) }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg sm:rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] sm:text-xs font-bold shadow-md transition"
                   title="{{ $selectedLocation ? 'Export data khusus lokasi ' . $selectedLocation : 'Export seluruh data stok TPM' }}">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Export Excel TPM {{ $selectedLocation ? "({$selectedLocation})" : '' }}</span>
                </a>
                <a href="{{ route('export.tpm.transactions') }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg sm:rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] sm:text-xs font-bold shadow-md transition">
                    <span>📜 Export Riwayat (IN/OUT)</span>
                </a>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="bg-[#242E49] border border-emerald-500/40 text-emerald-300 px-3 py-2 sm:px-4 sm:py-3 rounded-xl sm:rounded-2xl flex items-center gap-2 sm:gap-3 shadow-sm text-xs sm:text-sm">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Filter Bar: Pilihan Lokasi & Pencarian -->
        <div class="bg-[#242E49] p-4 sm:p-5 rounded-xl sm:rounded-2xl border border-[#37415C]/70 shadow-m3-1 space-y-3">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
                <form method="GET" action="{{ route('tpm.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 flex-1">
                    <!-- Dropdown Pilihan Lokasi -->
                    <div class="relative min-w-[220px]">
                        <select name="location" onchange="this.form.submit()"
                                class="w-full bg-[#181A2F] border border-[#37415C] text-white text-xs sm:text-sm rounded-xl px-3 py-2.5 focus:outline-none focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481]">
                            <option value="">🏢 Semua Lokasi ({{ $totalParts }} Part)</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc }}" {{ $selectedLocation === $loc ? 'selected' : '' }}>
                                    📍 Lokasi {{ $loc }} ({{ $locationCounts[$loc] ?? 0 }} Part)
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Input Pencarian -->
                    <div class="relative flex-1">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari part number atau nama barang..."
                               class="w-full bg-[#181A2F] border border-[#37415C] text-white text-xs sm:text-sm rounded-xl px-4 py-2.5 placeholder-slate-500 focus:outline-none focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481]">
                    </div>

                    <button type="submit" 
                            class="px-4 py-2.5 rounded-xl bg-[#181A2F] border border-[#37415C] hover:bg-[#37415C] text-white text-xs font-semibold transition shrink-0">
                        🔍 Cari
                    </button>

                    @if($selectedLocation || request('search'))
                        <a href="{{ route('tpm.index') }}" 
                           class="px-3 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-300 border border-rose-500/30 text-xs font-semibold transition text-center shrink-0">
                            ✕ Reset Filter
                        </a>
                    @endif
                </form>
            </div>

            <!-- Tab Pills Lokasi untuk Akses Cepat -->
            @if($locations->count() > 0)
                <div class="pt-3 border-t border-[#37415C]/50 flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <span class="text-slate-400 text-[11px] font-medium uppercase tracking-wider shrink-0 mr-1">
                        Filter Cepat:
                    </span>
                    <a href="{{ route('tpm.index', array_filter(['search' => request('search')])) }}" 
                       class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ empty($selectedLocation) ? 'bg-[#FDA481] text-[#181A2F] shadow-sm font-bold' : 'bg-[#181A2F] text-slate-300 border border-[#37415C] hover:text-white hover:border-[#FDA481]/50' }}">
                        <span>Semua Lokasi</span>
                        <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ empty($selectedLocation) ? 'bg-[#181A2F]/20 text-[#181A2F]' : 'bg-[#242E49] text-slate-300' }}">
                            {{ $totalParts }}
                        </span>
                    </a>
                    @foreach($locations as $loc)
                        <a href="{{ route('tpm.index', array_filter(['location' => $loc, 'search' => request('search')])) }}" 
                           class="shrink-0 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 {{ $selectedLocation === $loc ? 'bg-[#FDA481] text-[#181A2F] shadow-sm font-bold' : 'bg-[#181A2F] text-slate-300 border border-[#37415C] hover:text-white hover:border-[#FDA481]/50' }}">
                            <span>Lokasi {{ $loc }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $selectedLocation === $loc ? 'bg-[#181A2F]/20 text-[#181A2F]' : 'bg-[#242E49] text-slate-300' }}">
                                {{ $locationCounts[$loc] ?? 0 }}
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Banner Info Lokasi Terpilih -->
        @if($selectedLocation)
            <div class="flex items-center justify-between bg-[#181A2F]/80 border border-[#FDA481]/30 px-4 py-2.5 rounded-xl text-xs sm:text-sm text-slate-300">
                <div class="flex items-center gap-2">
                    <span class="text-[#FDA481] font-bold">📍 Lokasi: {{ $selectedLocation }}</span>
                    <span class="text-slate-400">({{ $tpmParts->count() }} part ditampilkan)</span>
                </div>
                <a href="{{ route('tpm.index', array_filter(['search' => request('search')])) }}" class="text-[#FDA481] hover:underline text-xs">
                    Tampilkan Semua Lokasi &rarr;
                </a>
            </div>
        @endif

        <!-- Petunjuk Scroll Khusus Layar HP -->
        <div class="block sm:hidden text-[10px] text-slate-400 italic flex items-center gap-1 mb-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            Geser tabel ke kanan untuk melihat detail
        </div>

        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Part Number</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Nama Barang TPM</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Lokasi</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Min / Max</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Stok</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @forelse($tpmParts as $part)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 font-mono font-bold text-white whitespace-nowrap">{{ $part->tpm_part_number }}</td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-200 font-semibold whitespace-nowrap">{{ $part->name }}</td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-400 whitespace-nowrap">
                                <a href="{{ route('tpm.index', ['location' => $part->location]) }}" 
                                   class="inline-block px-1.5 py-0.5 sm:px-2 sm:py-1 rounded bg-[#181A2F] border border-[#37415C] text-[10px] sm:text-xs text-[#FDA481] hover:border-[#FDA481] transition"
                                   title="Filter lokasi {{ $part->location }}">
                                    📍 {{ $part->location }}
                                </a>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-center font-semibold text-slate-500 whitespace-nowrap">
                                {{ $part->min_stock }} <span class="text-[#37415C]">/</span> {{ $part->max_stock }}
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                @if($part->stock <= $part->min_stock)
                                    <span class="inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                        {{ $part->stock }}
                                    </span>
                                @elseif($part->stock >= $part->max_stock)
                                    <span class="inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                        {{ $part->stock }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center justify-center min-w-[1.5rem] px-2 py-0.5 rounded-full text-[10px] sm:text-xs font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                        {{ $part->stock }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5 sm:gap-2">
                                    <a href="{{ route('tpm.scan', ['part_number' => $part->tpm_part_number]) }}" 
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#FDA481]/15 hover:bg-[#FDA481] text-[#FDA481] hover:text-[#181A2F] border border-[#FDA481]/30 text-[10px] sm:text-xs font-bold transition shadow-sm"
                                       title="Eksekusi Tambah / Ambil Stok untuk {{ $part->name }}">
                                        <span>⚡ Eksekusi</span>
                                    </a>
                                    @if(auth()->user()->role === 'admin')
                                    <form action="{{ route('tpm.destroy', $part->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[10px] sm:text-xs transition px-1 py-1" title="Hapus Master Part">Hapus</button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 sm:py-12 text-center text-slate-400 italic text-xs">
                                @if($selectedLocation)
                                    Tidak ada part TPM yang ditemukan untuk lokasi <strong>{{ $selectedLocation }}</strong>.
                                    <div class="mt-2">
                                        <a href="{{ route('tpm.index') }}" class="text-[#FDA481] hover:underline font-semibold">Tampilkan semua lokasi</a>
                                    </div>
                                @else
                                    Belum ada barang TPM.
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
