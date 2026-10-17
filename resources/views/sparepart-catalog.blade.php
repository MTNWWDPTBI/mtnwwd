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

        <!-- Kotak Filter & Pencarian Material Design 3 -->
        <div class="bg-[#242E49] p-6 rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <form action="{{ route('sparepart.catalog') }}" method="GET" x-data="filterDropdown()">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    
                    <!-- 1. Filter Pencarian Teks -->
                    <div class="col-span-1 md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Cari Sparepart</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nama part, kode SAP, nomor referensi..." 
                                   class="w-full pl-10 pr-4 py-2.5 bg-[#181A2F] border border-[#37415C] text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm transition">
                        </div>
                    </div>

                    <!-- 2. Filter Tipe Mesin -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Tipe Mesin</label>
                        <select name="machine_id" 
                                x-model="selectedMachine" 
                                x-on:change="fetchAssemblies()" 
                                class="w-full py-2.5 px-3 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm transition">
                            <option value="">-- Semua Mesin --</option>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}">{{ $machine->machine_name }} ({{ $machine->machine_code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Filter Assembly -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Part / Assembly</label>
                        <select name="assembly_id" 
                                x-model="selectedAssembly" 
                                class="w-full py-2.5 px-3 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm transition disabled:opacity-50 disabled:cursor-not-allowed" 
                                x-bind:disabled="assemblies.length === 0">
                            <option value="">-- Semua Assembly --</option>
                            <template x-for="assy in assemblies" :key="assy.id">
                                <option x-bind:value="assy.id" x-text="assy.assembly_name"></option>
                            </template>
                        </select>
                    </div>

                </div>
                
                <div class="mt-5 flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-[#37415C]/50">
                    <div class="flex items-center gap-2">
                        @if(request('machine_id') || request('assembly_id') || request('search'))
                            <span class="text-xs text-slate-400">Filter Aktif:</span>
                            @if(request('machine_id'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/30">
                                    Mesin: {{ optional($machines->firstWhere('id', request('machine_id')))->machine_name }}
                                </span>
                            @endif
                            @if(request('search'))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#B4182D]/20 text-[#FDA481] border border-[#B4182D]/40">
                                    "{{ request('search') }}"
                                </span>
                            @endif
                        @endif
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('sparepart.catalog') }}" 
                           class="px-4 py-2 rounded-xl bg-[#181A2F] hover:bg-[#37415C] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">
                            Reset Filter
                        </a>
                        <button type="submit" 
                                class="px-5 py-2 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                            Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Sparepart Material Design 3 -->
        <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Nama Part & Kode SAP</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Digunakan Di (Mesin & Assembly)</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Lokasi Bin / Rak Gudang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-sm">
                        @if(count($spareparts) > 0)
                            @foreach($spareparts as $part)
                            <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                                
                                <!-- 1. Nama Part & SAP -->
                                <td class="px-6 py-4">
                                    <div class="font-bold text-white text-sm sm:text-base tracking-tight">{{ $part->part_name }}</div>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-mono font-semibold bg-[#181A2F] text-slate-300 border border-[#37415C]">
                                            SAP: {{ $part->sap_code ?: '-' }}
                                        </span>
                                        <span class="text-[11px] text-slate-500 font-mono">
                                            ID: {{ $part->part_id }}
                                        </span>
                                    </div>
                                </td>

                                <!-- 2. Digunakan Di Mesin & Assembly -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1.5 max-w-md">
                                        @forelse($part->assemblies as $assy)
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-[#181A2F] text-slate-200 border border-[#37415C]/80">
                                                <span class="font-bold text-[#FDA481]">{{ $assy->machine->machine_name ?? 'Mesin' }}</span>
                                                <span class="text-slate-400">&bull;</span>
                                                <span>{{ $assy->assembly_name }}</span>
                                                <span class="text-[10px] px-1.5 py-0.2 rounded bg-[#37415C] text-slate-300 font-mono">
                                                    {{ $assy->pivot->qty }} {{ $assy->pivot->unit }}
                                                </span>
                                            </span>
                                        @empty
                                            <span class="text-xs text-slate-500 italic">Belum terhubung ke assembly</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- 3. Lokasi Bin (Rak) -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(!empty($part->bin_location))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/40 shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $part->bin_location }}</span>
                                        </span>
                                    @elseif(!empty($part->inventory?->bin_location))
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#FDA481]/15 text-[#FDA481] border border-[#FDA481]/40 shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>{{ $part->inventory->bin_location }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs italic bg-[#37415C]/40 text-slate-400 border border-[#37415C]">
                                            Belum ada lokasi
                                        </span>
                                    @endif
                                </td>

                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="px-6 py-12 text-center text-slate-400">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-[#181A2F] flex items-center justify-center text-slate-500 mb-3">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                    <div class="font-semibold text-white">Tidak ada data sparepart yang sesuai</div>
                                    <p class="text-xs text-slate-500 mt-1">Coba gunakan kata kunci pencarian lain atau klik Reset Filter.</p>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <!-- Pagination Container -->
            <div class="px-6 py-4 bg-[#181A2F]/60 border-t border-[#37415C]/60">
                {{ $spareparts->links() }}
            </div>
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