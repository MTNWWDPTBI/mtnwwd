<x-app-layout>
    <div class="space-y-4 sm:space-y-6 pb-8 sm:pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2 sm:gap-3">
                    <span>Shift Handover</span>
                    <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Logbook
                    </span>
                </h1>
                <p class="text-[10px] sm:text-sm text-slate-400 mt-1">
                    Catat dan pantau problem mesin antar shift.
                </p>
            </div>
            <div>
                <a href="{{ route('handover.create') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Input Problem Baru</span>
                </a>
                
                @if(auth()->user()->role === 'admin')
                <a href="{{ route('export.handover') }}" 
                   class="inline-flex items-center gap-1.5 sm:gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-[#181A2F] border border-[#FDA481]/50 hover:bg-[#FDA481]/10 text-[#FDA481] text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-3 h-3 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export Excel Handover</span>
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

        <div class="block sm:hidden text-[10px] text-slate-400 italic flex items-center gap-1 mb-1">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            Geser tabel ke kanan untuk melihat detail
        </div>

        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Tgl, Shift & Tim</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Mesin</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] min-w-[200px]">Detail Problem</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Status & Pelapor</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Aksi Lanjutan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @forelse($handovers as $log)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150 {{ $log->status == 'OPEN' ? 'bg-[#37415C]/10' : 'opacity-70' }}">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                <div class="font-bold text-white">{{ \Carbon\Carbon::parse($log->handover_date)->format('d M Y') }}</div>
                                <div class="text-[10px] text-slate-400 mt-1 flex gap-1.5">
                                    <span class="inline-block px-1.5 py-0.5 rounded bg-[#181A2F] border border-[#37415C]">Shift {{ $log->shift }}</span>
                                    <span class="inline-block px-1.5 py-0.5 rounded bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30 font-bold">Tim {{ $log->team }}</span>
                                </div>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                <div class="font-bold text-[#FDA481]">{{ $log->machine_name }}</div>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4">
                                <div class="text-slate-300 break-words text-[10px] sm:text-xs">
                                    {{ $log->problem_description }}
                                </div>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                @if($log->status == 'OPEN')
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 mb-1">
                                        🔴 OPEN
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 mb-1">
                                        🟢 CLOSED
                                    </span>
                                    <div class="text-[9px] sm:text-[10px] text-slate-400 mt-1">Selesai: <strong class="text-white">{{ $log->resolved_by }}</strong></div>
                                @endif
                                <div class="text-[9px] sm:text-[10px] text-slate-400 mt-1">Pelapor: <strong class="text-white">{{ $log->reported_by }}</strong></div>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                @if($log->status == 'OPEN')
                                    <form action="{{ route('handover.resolve', $log->id) }}" method="POST" onsubmit="return confirm('Sudah diselesaikan?');">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-2 py-1.5 sm:px-3 sm:py-1.5 rounded-lg bg-[#37415C]/50 hover:bg-[#37415C] border border-[#37415C] text-emerald-400 text-[10px] sm:text-xs font-bold transition">
                                            ✅ Selesai
                                        </button>
                                    </form>
                                @else
                                    <span class="text-slate-500 text-[10px] sm:text-xs font-bold italic">Clear 👍</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-8 sm:py-12 text-center text-slate-400 italic text-xs">Belum ada catatan logbook.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>