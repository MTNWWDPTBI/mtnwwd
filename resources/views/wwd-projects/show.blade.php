<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <div class="bg-[#242E49] border border-[#37415C] rounded-[24px] p-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('wwd-projects.index') }}" class="text-[#FDA481] text-xs font-bold hover:underline mb-2 inline-block">&larr; Kembali ke Dashboard</a>
                <h1 class="text-2xl font-bold text-white">{{ $project->project_name }}</h1>
                <p class="text-sm text-slate-400 mt-1">Target: {{ \Carbon\Carbon::parse($project->target_date)->format('d M Y') }} &bull; PIC: {{ $project->pic_leader }}</p>
            </div>
            <@if(auth()->user()->role === 'admin')
            <div class="mt-4 md:mt-0">
                <a href="{{ route('export.wwd_project', $project->id) }}" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-bold shadow-md transition duration-200 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Export ke Excel (CSV)
                </a>
            </div>
            @endif
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- TABEL EXCEL-LIKE -->
        <div class="bg-[#181A2F] border border-[#37415C] rounded-2xl overflow-hidden shadow-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-300">
                    <thead class="bg-[#242E49] text-xs uppercase text-slate-400 border-b border-[#37415C]">
                        <tr>
                            <th class="px-4 py-4 font-semibold">No. Mesin</th>
                            
                            <!-- Render Header Dinamis (Dari JSON) -->
                            @foreach($project->column_headers as $header)
                                <th class="px-4 py-4 font-semibold">{{ $header }}</th>
                            @endforeach
                            
                            <th class="px-4 py-4 font-semibold text-center">Status</th>
                            <th class="px-4 py-4 font-semibold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/50">
                        @foreach($project->details as $detail)
                            <tr class="hover:bg-[#242E49]/50 transition">
                                <!-- Form Update per Baris -->
                                <form action="{{ route('wwd-projects.details.update', $detail->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <td class="px-4 py-3 font-bold text-white">
                                        {{ $detail->machineUnit->machine_number }}
                                        <div class="text-[10px] font-normal text-slate-500">{{ $detail->machineUnit->machine->machine_name }}</div>
                                    </td>

                                    <!-- Render Input Dinamis (Sesuai JSON) -->
                                    @foreach($project->column_headers as $header)
                                        <td class="px-4 py-3">
                                            <input type="text" 
                                                   name="dynamic_data[{{ $header }}]" 
                                                   value="{{ $detail->dynamic_data[$header] ?? '' }}" 
                                                   class="w-full min-w-[120px] bg-[#181A2F] border border-[#37415C] focus:border-[#FDA481] text-white rounded-lg p-2 text-xs">
                                        </td>
                                    @endforeach

                                    <!-- Kolom Status -->
                                    <td class="px-4 py-3 text-center">
                                        <select name="status" class="bg-[#181A2F] border border-[#37415C] text-xs rounded-lg p-2 {{ $detail->status == 'CLOSE' ? 'text-emerald-400 border-emerald-500/50' : 'text-[#FDA481]' }}">
                                            <option value="OPEN" {{ $detail->status == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                                            <option value="CLOSE" {{ $detail->status == 'CLOSE' ? 'selected' : '' }}>CLOSE</option>
                                        </select>
                                    </td>

                                    <!-- Tombol Simpan -->
                                    <td class="px-4 py-3 text-right">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-[#37415C] text-white hover:bg-[#FDA481] hover:text-[#181A2F] text-xs font-bold transition">
                                            Update
                                        </button>
                                    </td>
                                </form>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>