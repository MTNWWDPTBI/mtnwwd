<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Header Halaman Katalog -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Katalog Suku Cadang</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        {{ number_format($spareparts->total()) }} Item
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Cari berdasarkan nama part, kode SAP, atau pilih filter hierarki Mesin dan Assembly.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#242E49] hover:bg-[#37415C] text-slate-200 text-xs font-semibold border border-[#37415C]/80 transition">
                    <svg class="w-4 h-4 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Dashboard</span>
                </a>
            </div>
        </div>

        <!-- Kotak Filter & Pencarian (Dibuat Compact untuk HP) -->
        <div class="bg-[#242E49] p-3 sm:p-6 rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <form action="{{ route('sparepart.catalog') }}" method="GET" x-data="filterDropdown()">
                <!-- Grid: Di HP menyusun ke bawah tapi rapat, di PC sejajar -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-2 sm:gap-4 items-end">
                    
                    <!-- 1. Filter Pencarian Teks -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 sm:mb-2">Cari Sparepart</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-2.5 sm:pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nama part, kode SAP..." 
                                   class="w-full pl-8 sm:pl-10 pr-3 sm:pr-4 py-1.5 sm:py-2.5 bg-[#181A2F] border border-[#37415C] text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-lg sm:rounded-xl text-[11px] sm:text-sm transition">
                        </div>
                    </div>

                    <!-- 2. Filter Tipe Mesin -->
                    <div>
                        <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 sm:mb-2">Tipe Mesin</label>
                        <select name="machine_id" 
                                x-model="selectedMachine" 
                                x-on:change="fetchAssemblies()" 
                                class="w-full py-1.5 sm:py-2.5 px-2 sm:px-3 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-lg sm:rounded-xl text-[11px] sm:text-sm transition">
                            <option value="">-- Semua Mesin --</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->machine_name }} ({{ $machine->machine_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Assembly -->
                    <div>
                        <label class="block text-[10px] sm:text-xs font-bold uppercase tracking-wider text-slate-300 mb-1 sm:mb-2">Part / Assembly</label>
                        <select name="assembly_id" 
                                x-model="selectedAssembly" 
                                class="w-full py-1.5 sm:py-2.5 px-2 sm:px-3 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-lg sm:rounded-xl text-[11px] sm:text-sm transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                x-bind:disabled="assemblies.length === 0">
                            <option value="">-- Semua Assembly --</option>
                            <template x-for="assy in assemblies" :key="assy.id">
                                <option x-bind:value="assy.id" x-text="assy.assembly_name"></option>
                            </template>
                        </select>
                    </div>

                </div>
                
                <div class="mt-3 sm:mt-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 sm:gap-3 pt-3 sm:pt-4 border-t border-[#37415C]/50">
                    <div class="flex items-center flex-wrap gap-1.5 sm:gap-2">
                        @if(request('machine_id') || request('assembly_id') || request('search'))
                            <span class="text-[10px] sm:text-xs text-slate-400">Filter:</span>
                            @if(request('machine_id'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30">
                                    Mesin: {{ optional($machines->firstWhere('id', request('machine_id')))->machine_name }}
                                </span>
                            @endif
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-full text-[10px] sm:text-xs font-semibold bg-[#B4182D]/20 text-[#FDA481] border border-[#B4182D]/40">
                                    "{{ request('search') }}"
                                </span>
                            @endif
                        @endif
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <a href="{{ route('sparepart.catalog') }}" 
                           class="flex-1 sm:flex-none text-center px-3 py-1.5 sm:px-4 sm:py-2 rounded-lg sm:rounded-xl bg-[#181A2F] hover:bg-[#37415C] text-slate-300 hover:text-white text-[10px] sm:text-xs font-semibold border border-[#37415C] transition">
                            Reset Filter
                        </a>
                        <button type="submit" 
                                class="flex-1 sm:flex-none text-center px-3 py-1.5 sm:px-5 sm:py-2 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-[10px] sm:text-xs font-bold tracking-wide shadow-md transition duration-200">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

       <!-- Tabel Daftar Sparepart (Bisa Scroll Vertikal & Horizontal di HP) -->
        <div class="block sm:hidden text-[10px] text-slate-400 italic flex items-center gap-1 mb-1 mt-4">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            Geser tabel ke kanan atau ke bawah untuk melihat detail
        </div>

        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden mt-0 sm:mt-6">
            
            <!-- PERUBAHAN UTAMA: Tambah overflow-y-auto dan max-h-[60vh] khusus HP -->
            <div class="overflow-x-auto overflow-y-auto max-h-[60vh] sm:max-h-none custom-scrollbar relative">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    
                    <!-- PERUBAHAN KEDUA: Jadikan thead "sticky" dan "z-10" agar header menempel saat di-scroll ke bawah -->
                    <thead class="bg-[#181A2F] sticky top-0 z-10 shadow-sm border-b border-[#37415C]/60">
                        <tr>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Nama Part & Kode SAP</th>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] min-w-[200px] sm:min-w-0">Digunakan Di (Mesin & Assembly)</th>
                            <th class="px-3 py-2 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase tracking-wider text-[#FDA481] whitespace-nowrap">Lokasi Bin / Rak Gudang</th>
                        </tr>
                    </thead>
                    
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @if(count($spareparts) > 0)
                            @foreach($spareparts as $part)
                            <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                                
                                <td class="px-3 py-2 sm:px-6 sm:py-4">
                                    <div class="font-bold text-white text-[11px] sm:text-base tracking-tight leading-tight">{{ $part->part_name }}</div>
                                    <div class="flex items-center gap-1.5 sm:gap-2 mt-1">
                                        <span class="inline-flex items-center px-1.5 py-0.5 sm:px-2 sm:py-0.5 rounded text-[9px] sm:text-[11px] font-mono font-semibold bg-[#181A2F] text-slate-300 border border-[#37415C]">
                                            SAP: {{ $part->sap_code ?: '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td class="px-3 py-2 sm:px-6 sm:py-4">
                                    <div class="flex flex-wrap gap-1 sm:gap-1.5 max-h-16 sm:max-h-full overflow-y-auto custom-scrollbar">
                                        @forelse($part->assemblies as $assy)
                                            <span class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 py-0.5 sm:px-2.5 sm:py-1 rounded sm:rounded-lg text-[9px] sm:text-xs font-medium bg-[#181A2F] text-slate-200 border border-[#37415C]/80">
                                                <span class="font-bold text-[#FDA481] truncate max-w-[80px] sm:max-w-none">{{ $assy->machine->machine_name ?? 'Mesin' }}</span>
                                                <span class="text-slate-400">&bull;</span>
                                                <span class="truncate max-w-[80px] sm:max-w-none">{{ $assy->assembly_name }}</span>
                                                <span class="text-[8px] sm:text-[10px] px-1 sm:px-1.5 py-0.2 rounded bg-[#37415C] text-slate-300 font-mono whitespace-nowrap">
                                                    {{ $assy->pivot->qty }} {{ $assy->pivot->unit }}
                                                </span>
                                            </span>
                                        @empty
                                            <span class="text-[9px] sm:text-xs text-slate-500 italic">Belum terhubung ke assembly</span>
                                        @endforelse
                                    </div>
                                </td>

                                <td class="px-3 py-2 sm:px-6 sm:py-4 whitespace-nowrap">
                                    @if(!empty($part->bin_location))
                                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/40 shadow-sm">
                                            <span>{{ $part->bin_location }}</span>
                                        </span>
                                    @elseif(!empty($part->inventory?->bin_location))
                                        <span class="inline-flex items-center gap-1 sm:gap-1.5 px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/40 shadow-sm">
                                            <span>{{ $part->inventory->bin_location }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[9px] sm:text-xs italic bg-[#37415C]/40 text-slate-400 border border-[#37415C]">
                                            Belum ada lokasi
                                        </span>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="px-3 py-8 sm:px-6 sm:py-12 text-center text-slate-400">
                                    <div class="font-semibold text-white text-xs sm:text-base">Tidak ada data sparepart yang sesuai</div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="px-3 py-3 sm:px-6 sm:py-4 bg-[#181A2F]/60 border-t border-[#37415C]/60 text-[10px] sm:text-sm">
                {{ $spareparts->links() }}
            </div>
        </div>

    <!-- Alpine.js untuk Filter Dropdown Dinamis -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('filterDropdown', () => ({
                selectedMachine: '{{ request('machine_id') }}',
                selectedAssembly: '{{ request('assembly_id') }}',
                assemblies: [],
                
                init() {
                    if (this.selectedMachine) {
                        this.fetchAssemblies(this.selectedAssembly);
                    }
                },
                
                fetchAssemblies(oldAssemblyId = '') {
                    this.assemblies = [];
                    this.selectedAssembly = ''; 
                    
                    if (!this.selectedMachine) return;

                    fetch(`/api/assemblies/${this.selectedMachine}`)
                        .then(res => res.json())
                        .then(data => {
                            this.assemblies = data;
                            if(oldAssemblyId) {
                                this.selectedAssembly = oldAssemblyId;
                            }
                        });
                }
            }))
        })
    </script>
</x-app-layout>