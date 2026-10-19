<x-app-layout>
    <div class="space-y-4 sm:space-y-6 pb-8 sm:pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2 sm:gap-3">
                    <span>Manajemen Master Sparepart</span>
                    <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Admin Master
                    </span>
                </h1>
                <p class="text-[10px] sm:text-sm text-slate-400 mt-1">
                    Tambah, perbarui, dan atur pemetaan sparepart ke assembly dan lokasi bin.
                </p>
            </div>
            <div>
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('spareparts.create') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Sparepart</span>
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
            Geser tabel ke kanan untuk melihat aksi
        </div>

        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Part Name & SAP</th>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Lokasi Bin</th>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] min-w-[200px] sm:min-w-0">Digunakan Pada Assembly</th>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @foreach($spareparts as $part)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                            <!-- 1. Kolom Nama Part & SAP -->
                            <td class="px-3 py-2 sm:px-6 sm:py-4">
                                <div class="font-bold text-white text-[11px] sm:text-sm leading-tight">{{ $part->part_name }}</div>
                                <div class="text-[9px] sm:text-xs text-slate-400 mt-1">SAP: {{ $part->sap_code ?: '-' }} &bull; ID: {{ $part->part_id }}</div>
                            </td>

                            <!-- 2. Kolom Lokasi Bin -->
                            <td class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">
                                @if(!empty($part->bin_location))
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30">
                                        {{ $part->bin_location }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-xs italic bg-[#37415C]/40 text-slate-400 border border-[#37415C]">
                                        Belum ada
                                    </span>
                                @endif
                            </td>

                            <!-- 3. Kolom Digunakan Pada Assembly -->
                            <!-- Dibatasi ketinggiannya di HP (max-h-16) agar tidak makan layar ke bawah -->
                            <td class="px-3 py-2 sm:px-6 sm:py-4">
                                <div class="max-h-16 sm:max-h-24 overflow-y-auto pr-1 sm:pr-2 space-y-1 custom-scrollbar">
                                    @forelse($part->assemblies as $assy)
                                        <div class="bg-[#181A2F] text-slate-200 border border-[#37415C] px-1.5 py-1 sm:px-2.5 rounded sm:rounded-lg text-[9px] sm:text-xs flex justify-between items-center gap-1 sm:gap-2">
                                            <span class="truncate"><strong class="text-[#FDA481]">{{ $assy->machine->machine_name ?? '' }}</strong> - {{ $assy->assembly_name }}</span>
                                            <span class="px-1 py-0.5 rounded bg-[#37415C] text-slate-300 font-mono whitespace-nowrap">({{ $assy->pivot->qty }} {{ $assy->pivot->unit }})</span>
                                        </div>
                                        <!-- Tampilkan Deskripsi Posisi jika terisi -->
                                            @if(!empty($assy->pivot->description))
                                                <div class="text-[9px] text-slate-400 italic border-t border-[#37415C]/50 pt-1 flex items-center gap-1">
                                                    <span class="text-[#FDA481] font-semibold">Posisi:</span> {{ $assy->pivot->description }}
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-slate-500 text-[10px] sm:text-xs italic">-</span>
                                    @endforelse
                                </div>
                            </td>

                            @if(auth()->user()->role === 'admin')
                            <!-- 4. Kolom Aksi -->
                            <td class="px-3 py-2 sm:px-6 sm:py-4 text-right whitespace-nowrap">
                                <a href="{{ route('spareparts.edit', $part->id) }}" 
                                   class="text-[#FDA481] hover:text-white font-semibold text-[10px] sm:text-xs mr-2 sm:mr-3 transition">Edit</a>
                                <form action="{{ route('spareparts.destroy', $part->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sparepart ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[10px] sm:text-xs transition">Hapus</button>
                                </form>
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <!-- Pagination Container: Diperkecil paddingnya untuk HP -->
            <div class="px-3 py-3 sm:px-6 sm:py-4 bg-[#181A2F]/60 border-t border-[#37415C]/60 text-[10px] sm:text-sm">
                {{ $spareparts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>