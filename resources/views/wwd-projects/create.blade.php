<x-app-layout>
    <div class="max-w-4xl mx-auto pb-12 space-y-6">
        <h1 class="text-2xl font-bold text-white mb-6">Setup Project Baru & Generate Mesin</h1>

        <form action="{{ route('wwd-projects.store') }}" method="POST" class="bg-[#242E49] border border-[#37415C] rounded-[24px] p-6 sm:p-8 shadow-lg">
            @csrf
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Nama Project / Problem</label>
                    <input type="text" name="project_name" required placeholder="Cth: Adjust Meja Geser" class="w-full bg-[#181A2F] border border-[#37415C] text-white rounded-xl p-3 text-sm focus:border-[#FDA481]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Tipe Mesin Target</label>
                    
                    <!-- KOTAK CHECKBOX -->
                    <div class="grid grid-cols-2 gap-2 bg-[#181A2F] border border-[#37415C] rounded-xl p-3 max-h-[140px] overflow-y-auto">
                        @foreach($machines as $machine)
                            <label class="flex items-center space-x-3 text-sm text-white cursor-pointer hover:bg-[#242E49] p-1.5 rounded-lg transition">
                                <!-- Perhatikan penambahan kurung siku [] pada name="machine_id[]" -->
                                <input type="checkbox" name="machine_id[]" value="{{ $machine->id }}" 
                                       class="w-4 h-4 rounded bg-[#242E49] border-[#37415C] text-[#B4182D] focus:ring-[#FDA481]">
                                <span>{{ $machine->machine_name }}</span>
                            </label>
                        @endforeach
                    </div>

                    <p class="text-[10px] text-[#FDA481] mt-1">*Bisa pilih lebih dari 1. Sistem akan menggenerate checklist ke semua unit fisik dari tipe yang dicentang.</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">PIC Leader</label>
                    <input type="text" name="pic_leader" placeholder="Nama PIC Utama" class="w-full bg-[#181A2F] border border-[#37415C] text-white rounded-xl p-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-400 uppercase mb-2">Target Selesai</label>
                    <input type="date" name="target_date" class="w-full bg-[#181A2F] border border-[#37415C] text-white rounded-xl p-3 text-sm">
                </div>
            </div>

            <hr class="border-[#37415C] my-8">

            <!-- FORM BUILDER KOLOM JSON -->
            <div class="mb-6">
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    Builder Kolom Laporan
                </h3>
                <p class="text-xs text-slate-400 mb-4">Tentukan kolom apa saja yang harus diisi teknisi (Cth: Temuan, Tgl Action, PIC Eksekusi, Remarks)</p>
                
                <div id="column-container" class="space-y-3">
                    <!-- Default Minimal 1 Kolom -->
                    <div class="flex items-center gap-3 column-row">
                        <input type="text" name="column_headers[]" value="Temuan" required class="flex-1 bg-[#181A2F] border border-[#37415C] text-white rounded-xl p-3 text-sm" placeholder="Nama Kolom...">
                        <button type="button" class="remove-col hidden px-3 py-3 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500/40">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <button type="button" id="add-col-btn" class="mt-4 px-4 py-2 rounded-xl border border-[#FDA481]/50 text-[#FDA481] text-xs font-bold hover:bg-[#FDA481]/10 transition">
                    + Tambah Kolom Lain
                </button>
            </div>

            <div class="mt-10 flex justify-end">
                <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] text-white font-bold hover:from-[#B4182D] hover:to-[#B4182D] shadow-lg transition duration-200">
                    Simpan & Generate Project
                </button>
            </div>
        </form>
    </div>

    <!-- Script Javascript Builder Kolom -->
    <script>
        document.getElementById('add-col-btn').addEventListener('click', function() {
            const container = document.getElementById('column-container');
            const row = document.createElement('div');
            row.className = 'flex items-center gap-3 column-row';
            row.innerHTML = `
                <input type="text" name="column_headers[]" required class="flex-1 bg-[#181A2F] border border-[#37415C] text-white rounded-xl p-3 text-sm" placeholder="Nama Kolom Baru...">
                <button type="button" class="remove-col px-3 py-3 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 hover:bg-rose-500/40" onclick="this.parentElement.remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;
            container.appendChild(row);
        });
    </script>
</x-app-layout>