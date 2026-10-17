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
                    Pantau limit stock dan kelola stock TPM.
                </p>
            </div>
            <div class="flex space-x-2 sm:space-x-3">
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
                <a href="{{ route('export.tpm') }}" 
                   class="inline-flex items-center gap-1.5 px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg sm:rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-[10px] sm:text-xs font-bold shadow-md transition">
                    <span>Export Excel TPM</span>
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
                            @if(auth()->user()->role === 'admin')
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-right whitespace-nowrap">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @forelse($tpmParts as $part)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 font-mono font-bold text-white whitespace-nowrap">{{ $part->tpm_part_number }}</td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-200 font-semibold whitespace-nowrap">{{ $part->name }}</td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-slate-400 whitespace-nowrap">
                                <span class="inline-block px-1.5 py-0.5 sm:px-2 sm:py-1 rounded bg-[#181A2F] border border-[#37415C] text-[10px] sm:text-xs">{{ $part->location }}</span>
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
                            @if(auth()->user()->role === 'admin')
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-right whitespace-nowrap">
                                <form action="{{ route('tpm.destroy', $part->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[10px] sm:text-xs transition">Hapus</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-3 py-8 sm:py-12 text-center text-slate-400 italic text-xs">Belum ada barang TPM.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>