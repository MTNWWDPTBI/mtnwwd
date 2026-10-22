<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- HEADER PROJECT -->
        <div class="bg-[#242E49] border border-[#37415C] rounded-[24px] p-6 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 shadow-xl">
            <div>
                <a href="{{ route('wwd-projects.index') }}" class="text-[#FDA481] text-xs font-bold hover:underline mb-2 inline-flex items-center gap-1.5 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Dashboard
                </a>
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ $project->project_name }}</h1>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider {{ $project->status == 'OPEN' ? 'bg-[#FDA481]/20 text-[#FDA481] border border-[#FDA481]/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' }}">
                        {{ $project->status }}
                    </span>
                </div>
                <p class="text-sm text-slate-400 mt-1">
                    Target: <span class="text-slate-200 font-medium">{{ \Carbon\Carbon::parse($project->target_date)->format('d M Y') }}</span> 
                    &bull; PIC: <span class="text-slate-200 font-medium">{{ $project->pic_leader ?? '-' }}</span>
                    &bull; Total: <span class="text-white font-semibold">{{ count($project->details) }}</span> Mesin
                </p>
            </div>
            
            <div class="flex items-center gap-3 flex-wrap w-full lg:w-auto justify-start lg:justify-end">
                <!-- Tombol Simpan Semua (Bulk Update) di Header -->
                <button type="submit" 
                        form="bulk-update-form" 
                        class="btn-submit-all px-5 py-2.5 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 active:scale-95 text-white text-sm font-bold shadow-lg shadow-emerald-900/30 transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Simpan Semua Data</span>
                </button>

                @if(auth()->user()->role === 'admin')
                <div>
                    <a href="{{ route('export.wwd_project', $project->id) }}" class="px-4 py-2.5 rounded-xl bg-[#37415C] hover:bg-[#475579] text-white text-sm font-bold shadow-md transition duration-200 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Export CSV
                    </a>
                </div>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold flex items-center gap-3 shadow-md animate-fade-in">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-semibold space-y-1 shadow-md">
                <div class="font-bold flex items-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Terjadi kesalahan validasi data:
                </div>
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- FORM UTAMA UNTUK UPDATE MASSAL (BULK UPDATE) -->
        <form action="{{ route('wwd-projects.bulk-update', $project->id) }}" method="POST" id="bulk-update-form">
            @csrf
            @method('PUT')

            <!-- TABEL EXCEL DENGAN FROZEN / STICKY HEADER & STICKY NO. MESIN -->
            <div class="bg-[#181A2F] border border-[#37415C] rounded-2xl shadow-2xl overflow-hidden">
                <!-- Hint scroll bar -->
                <div class="px-5 py-2.5 bg-[#20273f] border-b border-[#37415C] text-xs text-slate-300 flex items-center justify-between">
                    <span class="flex items-center gap-2">
                        <span class="text-[#FDA481]">📌</span>
                        <span>Kolom atas (header) dan No. Mesin telah <strong>difreeze/dibekukan</strong> saat scroll ke bawah atau ke samping.</span>
                    </span>
                    <span class="hidden sm:inline text-slate-400">Total: <strong>{{ count($project->details) }}</strong> baris</span>
                </div>

                <div class="max-h-[66vh] overflow-x-auto overflow-y-auto relative" style="scrollbar-gutter: stable;">
                    <table class="w-full text-left text-sm text-slate-300 border-separate border-spacing-0">
                        <!-- THEAD STICKY TOP -->
                        <thead class="sticky top-0 z-30 bg-[#242E49] text-xs uppercase text-slate-300 shadow-md">
                            <tr>
                                <!-- Kolom No Mesin: Freeze di atas dan di kiri (top-0 left-0) -->
                                <th scope="col" class="sticky top-0 left-0 z-40 bg-[#242E49] px-4 py-3.5 font-bold text-white border-b border-r border-[#37415C] whitespace-nowrap shadow-sm min-w-[140px]">
                                    No. Mesin
                                </th>
                                
                                <!-- Render Header Dinamis (Dari JSON Project) -->
                                @foreach($project->column_headers as $header)
                                    <th scope="col" class="sticky top-0 z-30 bg-[#242E49] px-4 py-3.5 font-semibold text-slate-200 border-b border-[#37415C] whitespace-nowrap min-w-[160px]">
                                        {{ $header }}
                                    </th>
                                @endforeach
                                
                                <th scope="col" class="sticky top-0 z-30 bg-[#242E49] px-4 py-3.5 font-semibold text-center border-b border-[#37415C] whitespace-nowrap min-w-[110px]">
                                    Status
                                </th>
                                <th scope="col" class="sticky top-0 z-30 bg-[#242E49] px-4 py-3.5 font-semibold text-right border-b border-[#37415C] whitespace-nowrap min-w-[90px]">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        
                        <tbody class="divide-y divide-[#37415C]/40 bg-[#181A2F]">
                            @forelse($project->details as $detail)
                                <tr class="hover:bg-[#242E49]/50 transition group">
                                    <!-- Kolom No. Mesin: Sticky Left agar tetap terlihat saat scroll horizontal -->
                                    <td class="sticky left-0 z-20 bg-[#181A2F] group-hover:bg-[#20273f] px-4 py-3 font-bold text-white border-b border-r border-[#37415C]/60 whitespace-nowrap transition shadow-sm">
                                        <div class="text-sm font-bold text-[#FDA481] group-hover:text-white transition">
                                            {{ $detail->machineUnit->machine_number }}
                                        </div>
                                        <div class="text-[11px] font-normal text-slate-400">
                                            {{ $detail->machineUnit->machine->machine_name }}
                                        </div>
                                    </td>

                                    <!-- Render Input Dinamis Sesuai Header Kolom -->
                                    @foreach($project->column_headers as $header)
                                        <td class="px-4 py-2.5 border-b border-[#37415C]/30">
                                            <input type="text" 
                                                   name="details[{{ $detail->id }}][dynamic_data][{{ $header }}]" 
                                                   value="{{ $detail->dynamic_data[$header] ?? '' }}" 
                                                   placeholder="Isi {{ $header }}..."
                                                   class="w-full min-w-[150px] bg-[#111322] border border-[#37415C] focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481] text-white rounded-lg px-3 py-2 text-xs transition placeholder:text-slate-600">
                                        </td>
                                    @endforeach

                                    <!-- Kolom Status (OPEN / CLOSE) -->
                                    <td class="px-4 py-2.5 text-center border-b border-[#37415C]/30 whitespace-nowrap">
                                        <select name="details[{{ $detail->id }}][status]" 
                                                class="bg-[#111322] border border-[#37415C] text-xs font-semibold rounded-lg px-2.5 py-1.5 transition {{ $detail->status == 'CLOSE' ? 'text-emerald-400 border-emerald-500/50' : 'text-[#FDA481]' }}">
                                            <option value="OPEN" {{ $detail->status == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                                            <option value="CLOSE" {{ $detail->status == 'CLOSE' ? 'selected' : '' }}>CLOSE</option>
                                        </select>
                                    </td>

                                    <!-- Tombol Simpan 1 Baris (Tetap Tersedia via HTML5 Formaction) -->
                                    <td class="px-4 py-2.5 text-right border-b border-[#37415C]/30 whitespace-nowrap">
                                        <button type="submit" 
                                                formaction="{{ route('wwd-projects.details.update', $detail->id) }}" 
                                                formmethod="POST" 
                                                title="Simpan baris mesin ini saja"
                                                class="px-3 py-1.5 rounded-lg bg-[#242E49] hover:bg-[#FDA481] hover:text-[#181A2F] text-slate-300 text-xs font-bold transition shadow border border-[#37415C] hover:border-[#FDA481]">
                                            Simpan
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($project->column_headers) + 3 }}" class="p-8 text-center text-slate-400">
                                        Tidak ada checklist unit mesin yang terhubung ke project ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FLOATING STICKY ACTION BAR DI BAWAH -->
            <div class="sticky bottom-4 z-20 mt-6 bg-[#242E49]/95 backdrop-blur-md border border-[#37415C] rounded-2xl px-5 sm:px-6 py-4 shadow-2xl flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 flex items-center justify-center font-bold text-lg shadow-sm">
                        ⚡
                    </div>
                    <div>
                        <p class="text-sm font-bold text-white">Bulk Update Checklist Mesin</p>
                        <p class="text-xs text-slate-400">Anda dapat mengisi seluruh baris data di atas, lalu simpan semuanya sekaligus dalam satu kali klik.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <button type="submit" 
                            class="btn-submit-all w-full sm:w-auto px-6 py-3 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-500 hover:from-emerald-500 hover:to-teal-400 active:scale-95 text-white text-sm font-bold shadow-lg shadow-emerald-900/30 transition duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        <span>Update & Simpan Semua Data</span>
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Script visual feedback saat submit form bulk -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const bulkForm = document.getElementById('bulk-update-form');
            if (bulkForm) {
                bulkForm.addEventListener('submit', function (e) {
                    const submitter = e.submitter;
                    // Jika yang diklik adalah tombol bulk update
                    if (submitter && submitter.classList.contains('btn-submit-all')) {
                        const allBtns = document.querySelectorAll('.btn-submit-all');
                        allBtns.forEach(btn => {
                            btn.disabled = true;
                            btn.innerHTML = `
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Menyimpan Semua Data...
                            `;
                        });
                    }
                });
            }
        });
    </script>
</x-app-layout>
