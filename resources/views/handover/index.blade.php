<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Header Halaman -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-[#37415C]/60">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Input Problem Shift</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Logbook
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Catat dan pantau problem mesin antar shift maintenance secara langsung.
                </p>
            </div>

            @if(auth()->user()?->role === 'admin')
                <div class="flex items-center gap-2">
                    <a href="{{ route('export.handover') }}" 
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#181A2F] border border-[#FDA481]/50 hover:bg-[#FDA481]/10 text-[#FDA481] text-xs font-bold tracking-wide shadow-md transition duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <span>Export Excel Handover</span>
                    </a>
                </div>
            @endif
        </div>

        <!-- Alert Notifikasi -->
        <div>
            @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-300 text-sm flex items-center gap-3 shadow-md">
                    <svg class="w-5 h-5 shrink-0 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-5 p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-300 text-sm shadow-md">
                    <p class="font-bold mb-1">Perhatian Form Input:</p>
                    <ul class="list-disc list-inside space-y-1 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <!-- ======================================================== -->
        <!-- FORM SINGLE INPUT PROBLEM SHIFT (CARD)                   -->
        <!-- ======================================================== -->
        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="p-6 sm:p-8">
                <form action="{{ route('handover.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <!-- Baris 1: Tanggal Shift, Shift Ke-, dan Grup Tim -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <!-- Tanggal Shift -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">
                                TANGGAL SHIFT <span class="text-rose-400">*</span>
                            </label>
                            <input type="date" 
                                   name="handover_date" 
                                   required 
                                   value="{{ old('handover_date', date('Y-m-d')) }}"
                                   style="background-color: #181A2F !important; color: #FFFFFF !important;"
                                   class="w-full !bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 [color-scheme:dark] transition">
                        </div>

                        <!-- Shift Ke- -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">
                                SHIFT KE- <span class="text-rose-400">*</span>
                            </label>
                            <select name="shift" 
                                    required 
                                    style="background-color: #181A2F !important; color: #FFFFFF !important;"
                                    class="w-full !bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 transition">
                                <option value="" class="bg-[#181A2F] text-slate-400">-- Pilih Shift --</option>
                                <option value="1" class="bg-[#181A2F] text-white" {{ old('shift') == '1' ? 'selected' : '' }}>Shift 1 (Pagi)</option>
                                <option value="2" class="bg-[#181A2F] text-white" {{ old('shift') == '2' ? 'selected' : '' }}>Shift 2 (Siang)</option>
                                <option value="3" class="bg-[#181A2F] text-white" {{ old('shift') == '3' ? 'selected' : '' }}>Shift 3 (Malam)</option>
                            </select>
                        </div>

                        <!-- Grup Tim -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">
                                GRUP TIM
                            </label>
                            <select name="team" 
                                    style="background-color: #181A2F !important; color: #FFFFFF !important;"
                                    class="w-full !bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 transition">
                                <option value="" class="bg-[#181A2F] text-slate-400">-- Pilih Tim --</option>
                                <option value="A" class="bg-[#181A2F] text-white" {{ old('team') == 'A' ? 'selected' : '' }}>Tim A</option>
                                <option value="B" class="bg-[#181A2F] text-white" {{ old('team') == 'B' ? 'selected' : '' }}>Tim B</option>
                                <option value="C" class="bg-[#181A2F] text-white" {{ old('team') == 'C' ? 'selected' : '' }}>Tim C</option>
                                <option value="D" class="bg-[#181A2F] text-white" {{ old('team') == 'D' ? 'selected' : '' }}>Tim D</option>
                            </select>
                        </div>
                    </div>

                    <!-- Baris 2: Nama Mesin / Fasilitas -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">
                            NAMA MESIN / FASILITAS <span class="text-rose-400">*</span>
                        </label>
                        <input type="text" 
                               name="machine_name" 
                               required 
                               placeholder="CONTOH: MESIN COMPRESSOR 1, POMPA AIR..." 
                               value="{{ old('machine_name') }}"
                               style="background-color: #181A2F !important; color: #FFFFFF !important;"
                               class="w-full !bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 uppercase transition">
                    </div>

                    <!-- Baris 3: Detail Problem / Temuan -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">
                            DETAIL PROBLEM / TEMUAN <span class="text-rose-400">*</span>
                        </label>
                        <textarea name="problem_description" 
                                  rows="3" 
                                  required 
                                  placeholder="Jelaskan kendala apa yang terjadi, apa yang sudah dilakukan..."
                                  style="background-color: #181A2F !important; color: #FFFFFF !important;"
                                  class="w-full !bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 transition">{{ old('problem_description') }}</textarea>
                    </div>

                    <!-- Baris 4: LOTOTO CHECKIN/CHECKOUT -->
                    <div class="p-4 bg-[#181A2F] border border-[#37415C] rounded-xl">
                        <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-1 flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span>LOTOTO CHECKIN/CHECKOUT</span>
                            <span class="text-rose-400">*</span>
                        </label>
                        <p class="text-[11px] text-slate-400 mb-3">
                            Apakah mesin/area ini dipasangi gembok dan tag pengaman LOTOTO aktif?
                        </p>
                        <div class="flex items-center gap-6">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" 
                                       name="lototo_status" 
                                       value="Belum" 
                                       {{ old('lototo_status', 'Belum') === 'Belum' ? 'checked' : '' }}
                                       class="w-4 h-4 text-[#B4182D] bg-[#181A2F] border-[#37415C] focus:ring-[#B4182D]">
                                <span class="text-xs font-semibold text-slate-300">Belum (Tidak Ada LOTOTO)</span>
                            </label>
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="radio" 
                                       name="lototo_status" 
                                       value="Sudah" 
                                       {{ old('lototo_status') === 'Sudah' ? 'checked' : '' }}
                                       class="w-4 h-4 text-[#B4182D] bg-[#181A2F] border-[#37415C] focus:ring-[#B4182D]">
                                <span class="text-xs font-bold text-[#FDA481]">Sudah (LOTOTO Terpasang)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Tombol Aksi Form -->
                    <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-6 border-t border-[#37415C]/60">
                        <button type="reset" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#37415C]/50 hover:bg-[#37415C] text-slate-300 text-xs font-bold transition">
                            Batal
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span>Simpan & Oper</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ======================================================== -->
        <!-- SECTION RIWAYAT LOGBOOK DI BAWAH FORM                    -->
        <!-- ======================================================== -->
        <div class="space-y-4 pt-2">
            <div class="flex items-center justify-between pb-2">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg sm:text-xl font-extrabold text-white tracking-tight flex items-center gap-2">
                        <span>Riwayat Logbook Problem Shift Terakhir</span>
                        <span class="text-[10px] sm:text-xs font-semibold px-2.5 py-0.5 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                            10 Problem Terkini
                        </span>
                    </h2>
                </div>
            </div>

            <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                        <thead class="bg-[#181A2F]/90">
                            <tr>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Tgl, Shift & Tim</th>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Mesin</th>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] min-w-[200px]">Detail Problem</th>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">LOTOTO</th>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Status & Pelapor</th>
                                <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] text-center whitespace-nowrap">Aksi Lanjutan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                            @forelse($handovers as $log)
                                <tr class="hover:bg-[#181A2F]/40 transition duration-150 {{ $log->status == 'OPEN' ? 'bg-[#37415C]/10' : 'opacity-70' }}">
                                    <!-- Tgl, Shift & Tim -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                        <div class="font-bold text-white">{{ \Carbon\Carbon::parse($log->handover_date)->format('d M Y') }}</div>
                                        <div class="text-[10px] text-slate-400 mt-1 flex gap-1.5">
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#181A2F] border border-[#37415C]">Shift {{ $log->shift }}</span>
                                            <span class="inline-block px-1.5 py-0.5 rounded bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30 font-bold">Tim {{ $log->team }}</span>
                                        </div>
                                    </td>

                                    <!-- Mesin -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                        <div class="font-bold text-[#FDA481]">{{ $log->machine_name }}</div>
                                    </td>

                                    <!-- Detail Problem -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4">
                                        <div class="text-slate-300 break-words text-xs leading-relaxed">
                                            {{ $log->problem_description }}
                                        </div>
                                    </td>

                                    <!-- LOTOTO -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                        @if($log->lototo_status === 'Sudah')
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30 shadow-sm">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                SUDAH
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-md text-[10px] font-bold bg-[#B4182D]/20 text-red-400 border border-[#B4182D]/50 shadow-sm animate-pulse">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                                BELUM
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Status & Pelapor -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                        @if($log->status == 'OPEN')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30 mb-1">
                                                🔴 OPEN
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 mb-1">
                                                🟢 CLOSED
                                            </span>
                                        @endif
                                        <div class="text-[10px] text-slate-400">
                                            Lapor: {{ $log->reported_by }}
                                        </div>
                                    </td>

                                    <!-- Aksi Lanjutan -->
                                    <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                        <div class="flex items-center justify-center gap-2">
                                            @if($log->status == 'OPEN')
                                                <form action="{{ route('handover.resolve', $log->id) }}" method="POST" onsubmit="return confirm('Apakah problem pada mesin {{ $log->machine_name }} sudah benar-benar selesai diperbaiki?')">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600/90 hover:bg-emerald-600 text-white text-xs font-bold transition shadow-sm">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Selesai</span>
                                                    </button>
                                                </form>
                                            @else
                                                <div class="text-[11px] text-slate-400">
                                                    <div>Selesai oleh:</div>
                                                    <div class="font-bold text-slate-300">{{ $log->resolved_by ?? 'Teknisi' }}</div>
                                                    <div class="text-[10px] text-slate-500">{{ $log->resolved_at ? \Carbon\Carbon::parse($log->resolved_at)->format('d/m/Y H:i') : '' }}</div>
                                                </div>
                                            @endif

                                            @if(auth()->user()?->role === 'admin')
                                                <form action="{{ route('handover.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Admin: Yakin ingin menghapus history problem mesin ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            title="Hapus problem mesin (Khusus Admin)" 
                                                            class="p-1.5 text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 rounded-lg transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-slate-400 text-xs">
                                        Belum ada history problem mesin di logbook.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
