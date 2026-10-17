<x-app-layout>
    <div class="space-y-6 pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Data Master Mesin</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        {{ $machines->total() }} Mesin
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Kelola kode mesin, tipe mesin, dan foto ilustrasi unit.
                </p>
            </div>
            <div>
                <a href="{{ route('machines.create') }}" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Mesin</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-[#242E49] border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="bg-[#242E49] border border-rose-500/40 text-rose-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-sm font-medium">{{ $errors->first() }}</span>
            </div>
        @endif

        <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Visual Mesin</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Kode Mesin</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Nama & Tipe Mesin</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481]">Aksi Cepat</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-[#FDA481] text-right">Kelola</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-sm">
                        @foreach($machines as $machine)
                        <tr class="hover:bg-[#181A2F]/40 transition duration-150">
                            <!-- Visual Mesin Thumbnail -->
                            <td class="px-6 py-3 whitespace-nowrap">
                                <div class="w-16 h-10 rounded-lg overflow-hidden bg-[#181A2F] border border-[#37415C] shadow-sm flex items-center justify-center">
                                    <img src="{{ $machine->image_url }}" alt="{{ $machine->machine_name }}" class="w-full h-full object-cover">
                                </div>
                            </td>

                            <!-- Kode Mesin -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-[#181A2F] text-[#FDA481] border border-[#FDA481]/30">
                                    {{ $machine->machine_code }}
                                </span>
                            </td>

                            <!-- Nama Mesin -->
                            <td class="px-6 py-4">
                                <span class="font-bold text-white text-base">{{ $machine->machine_name }}</span>
                            </td>

                            <!-- Aksi Cepat ke Sparepart -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('sparepart.catalog', ['machine_id' => $machine->id]) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-[#181A2F] text-[#FDA481] hover:bg-[#B4182D] hover:text-white border border-[#37415C] transition">
                                    <span>Buka Suku Cadang</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </td>

                            <!-- Kelola CRUD -->
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('machines.edit', $machine->id) }}" 
                                   class="text-[#FDA481] hover:text-white font-semibold text-xs mr-3 transition">Edit</a>
                                <form action="{{ route('machines.destroy', $machine->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus mesin ini?');">
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
                {{ $machines->links() }}
            </div>
        </div>
    </div>
</x-app-layout>