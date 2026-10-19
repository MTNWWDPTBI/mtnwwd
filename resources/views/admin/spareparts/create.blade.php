<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6 pb-12">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Tambah Master Sparepart</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Masukkan informasi suku cadang baru dan tentukan lokasinya.</p>
        </div>

        <div class="bg-[#242E49] p-6 sm:p-8 rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <form action="{{ route('spareparts.store') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kode SAP</label>
                    <input type="text" name="sap_code" placeholder="Misal: 2559056" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Sparepart (Part Name)</label>
                    <input type="text" name="part_name" placeholder="Misal: FLANGE, BEARING 6204, dll" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Lokasi Rak / Bin</label>
                    <input type="text" name="bin_location" placeholder="Misal: B 21 B4, RAK-A1" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Digunakan pada Assembly</label>
                    <select name="assembly_id" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                        <option value="">-- Pilih Assembly / Mesin --</option>
                        @foreach($assemblies as $assy)
                            <option value="{{ $assy->id }}">{{ $assy->machine->machine_name ?? '' }} ➔ {{ $assy->assembly_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kebutuhan (Qty)</label>
                        <input type="number" name="qty" value="1" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Satuan (Unit)</label>
                        <input type="text" name="unit" value="Pcs" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                    </div>
                </div>

                <!-- Tambahan Field Deskripsi Posisi -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Deskripsi Posisi (Opsional)</label>
                    <input type="text" name="description" placeholder="Misal: Posisi Spool Holder Kiri, Top Frame, dll" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#37415C]/50">
                    <a href="{{ route('spareparts.index') }}" class="px-5 py-2.5 rounded-xl bg-[#181A2F] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">Batal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">Simpan Sparepart</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>