<x-app-layout>
    <div class="space-y-6 pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Manajemen Master Sparepart</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Admin Master
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Tambah, perbarui, dan atur pemetaan sparepart ke assembly dan lokasi bin.
                </p>
            </div>
            <div>
                <a href="{{ route('spareparts.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Sparepart</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-[#242E49] border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Part Name & SAP</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Lokasi Bin</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Digunakan Pada Assembly</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481] text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-sm">
                        @foreach($spareparts as $part)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                            <!-- 1. Kolom Nama Part & SAP -->
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-white">{{ $part->part_name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">SAP: {{ $part->sap_code ?: '-' }} &bull; ID: {{ $part->part_id }}</div>
                            </td>

                            <!-- 2. Kolom Lokasi Bin -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(!empty($part->bin_location))
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30">
                                        {{ $part->bin_location }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs italic bg-[#37415C]/40 text-slate-400 border border-[#37415C]">
                                        Belum ada lokasi
                                    </span>
                                @endif
                            </td>

                            <!-- 3. Kolom Digunakan Pada Assembly -->
                            <td class="px-6 py-4">
                                <div class="max-h-24 overflow-y-auto pr-2 space-y-1">
                                    @forelse($part->assemblies as $assy)
                                        <div class="bg-[#181A2F] text-slate-200 border border-[#37415C] px-2.5 py-1 rounded-lg text-xs flex justify-between items-center gap-2">
                                            <span><strong class="text-[#FDA481]">{{ $assy->machine->machine_name ?? '' }}</strong> - {{ $assy->assembly_name }}</span>
                                            <span class="text-[10px] px-1.5 py-0.2 rounded bg-[#37415C] text-slate-300 font-mono">({{ $assy->pivot->qty }} {{ $assy->pivot->unit }})</span>
                                        </div>
                                    @empty
                                        <span class="text-slate-500 text-xs italic">-</span>
                                    @endforelse
                                </div>
                            </td>

                            <!-- 4. Kolom Aksi -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('spareparts.edit', $part->id) }}" 
                                   class="text-[#FDA481] hover:text-white font-semibold text-xs mr-3 transition">Edit</a>
                                <form action="{{ route('spareparts.destroy', $part->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sparepart ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-xs transition">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 bg-[#181A2F]/60 border-t border-[#37415C]/60">
                {{ $spareparts->links() }}
            </div>
        </div>
    </div>
</x-app-layout>