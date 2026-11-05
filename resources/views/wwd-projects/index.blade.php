<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Header -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Project & Improvement WWD</h1>
                <p class="text-sm text-slate-400 mt-1">Pantau progress eksekusi project pada setiap unit mesin.</p>
            </div>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('wwd-projects.create') }}" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-sm font-bold shadow-md transition duration-200 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Project Baru
            </a>
            @endif
        </div>

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Grid Project -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            @forelse($projects as $project)
                @php
                    $progress = $project->total_machines > 0 ? round(($project->closed_machines / $project->total_machines) * 100) : 0;
                @endphp
                <div class="bg-[#242E49] border border-[#37415C]/60 hover:border-[#FDA481]/50 rounded-[22px] p-5 transition duration-300 group flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start mb-4 gap-2">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $project->status == 'OPEN' ? 'bg-[#FDA481]/20 text-[#FDA481]' : 'bg-emerald-500/20 text-emerald-400' }}">
                                {{ $project->status }}
                            </span>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-slate-400 font-medium">{{ \Carbon\Carbon::parse($project->target_date)->format('d M Y') }}</span>
                                @if(auth()->user()->role === 'admin')
                                <form action="{{ route('wwd-projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus project \'{{ addslashes($project->project_name) }}\'? Seluruh data checklist mesin di dalamnya akan ikut terhapus.')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-400 hover:bg-rose-500/10 transition" title="Hapus Project (Admin)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                        
                        <a href="{{ route('wwd-projects.show', $project->id) }}" class="block">
                            <h3 class="text-lg font-bold text-white group-hover:text-[#FDA481] transition line-clamp-2">{{ $project->project_name }}</h3>
                            <p class="text-xs text-slate-400 mt-1 line-clamp-1">PIC: {{ $project->pic_leader ?? 'Belum ada PIC' }}</p>
                        </a>
                    </div>

                    <!-- Progress Bar -->
                    <a href="{{ route('wwd-projects.show', $project->id) }}" class="block mt-6">
                        <div class="flex justify-between text-xs font-semibold mb-2">
                            <span class="text-white">Progress</span>
                            <span class="text-[#FDA481]">{{ $progress }}% ({{ $project->closed_machines }}/{{ $project->total_machines }})</span>
                        </div>
                        <div class="w-full bg-[#181A2F] rounded-full h-2.5 border border-[#37415C]">
                            <div class="bg-gradient-to-r from-[#B4182D] to-[#FDA481] h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </a>
                </div>
            @empty
                <div class="col-span-full p-8 text-center bg-[#242E49] border border-[#37415C] rounded-2xl text-slate-400">
                    Belum ada project yang dibuat.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>