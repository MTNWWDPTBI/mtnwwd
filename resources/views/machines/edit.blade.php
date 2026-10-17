<x-app-layout>
    <div class="max-w-xl mx-auto space-y-6 pb-12">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Edit Mesin</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Perbarui kode mesin, nama mesin, atau ganti foto unit.</p>
        </div>

        <div class="bg-[#242E49] p-6 sm:p-8 rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <form action="{{ route('machines.update', $machine->id) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kode Mesin</label>
                    <input type="text" name="machine_code" value="{{ $machine->machine_code }}" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Mesin</label>
                    <input type="text" name="machine_name" value="{{ $machine->machine_name }}" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Foto / Ilustrasi Saat Ini</label>
                    <div class="flex items-center gap-4 p-3 bg-[#181A2F] border border-[#37415C] rounded-xl mb-3">
                        <div class="w-20 h-14 rounded-lg overflow-hidden bg-[#242E49] border border-[#37415C] flex-shrink-0">
                            <img src="{{ $machine->image_url }}" alt="{{ $machine->machine_name }}" class="w-full h-full object-cover">
                        </div>
                        <div class="text-xs text-slate-400">
                            <span class="font-semibold text-white block">{{ $machine->image ? 'Foto Custom Tersimpan' : 'Visual Ilustrasi Standar' }}</span>
                            <span class="text-[11px] text-slate-500">Unggah file baru jika ingin mengganti foto mesin.</span>
                        </div>
                    </div>

                    <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FDA481]/20 file:text-[#FDA481] hover:file:bg-[#FDA481]/30 cursor-pointer bg-[#181A2F] border border-[#37415C] rounded-xl p-2">
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#37415C]/50">
                    <a href="{{ route('machines.index') }}" class="px-5 py-2.5 rounded-xl bg-[#181A2F] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">Batal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">Perbarui Mesin</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>