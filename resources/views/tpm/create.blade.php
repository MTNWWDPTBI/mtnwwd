<x-app-layout>
    <div class="space-y-6 pb-12">
        <div class="max-w-3xl mx-auto">
            
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Daftar Barang TPM Baru</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Generate 999x
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Sistem akan otomatis memberikan kode part berawalan 999x untuk barang ini.
                </p>
            </div>

            <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <form action="{{ route('tpm.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Nama Barang TPM</label>
                            <input type="text" name="name" required placeholder="Contoh: MAJUN, WD-40, SARUNG TANGAN..." 
                                   class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 uppercase">
                        </div>

                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Lokasi Rak</label>
                            <input type="text" name="location" required placeholder="Contoh: G1, G5..." 
                                   class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 uppercase">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Minimum Stok</label>
                                <input type="number" name="min_stock" required min="0" placeholder="Batas Bawah. Contoh: 5" 
                                       class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Maksimum Stok</label>
                                <input type="number" name="max_stock" required min="1" placeholder="Batas Atas. Contoh: 20" 
                                       class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#37415C]/60">
                            <a href="{{ route('tpm.index') }}" 
                               class="px-5 py-2.5 rounded-xl bg-[#37415C]/50 hover:bg-[#37415C] text-slate-300 text-xs font-bold transition">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                                Simpan Master Barang
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>