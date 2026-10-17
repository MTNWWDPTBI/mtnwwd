<x-app-layout>
    <div class="space-y-6 pb-12">
        <div class="max-w-3xl mx-auto">
            
            <!-- Bagian Header / Judul Form -->
            <div class="mb-6">
                <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                    <span>Input Problem Shift</span>
                    <span class="text-xs font-semibold px-3 py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Logbook
                    </span>
                </h1>
                <p class="text-xs sm:text-sm text-slate-400 mt-1">
                    Catat temuan atau kendala mesin untuk dioper ke shift selanjutnya.
                </p>
            </div>

            <!-- Card Form dengan Gaya Dark Mode -->
            <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
                <div class="p-6 sm:p-8">
                    <form action="{{ route('handover.store') }}" method="POST">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Tanggal -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Tanggal Shift</label>
                                <!-- Tambahan [color-scheme:dark] agar kalender popup ikut mode gelap -->
                                <input type="date" name="handover_date" required value="{{ date('Y-m-d') }}" 
                                       class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 [color-scheme:dark]">
                            </div>
                            
                            <!-- Pilihan Shift -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Shift Ke-</label>
                                <select name="shift" required 
                                        class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3">
                                    <option value="1">Shift 1 (Pagi)</option>
                                    <option value="2">Shift 2 (Siang)</option>
                                    <option value="3">Shift 3 (Malam)</option>
                                </select>
                            </div>
                            <!-- Pilihan Tim (Baru) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Grup Tim</label>
                                <select name="team" required 
                                        class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3">
                                    <option value="" class="text-slate-500">-- Pilih Tim --</option>
                                    <option value="A">Tim A</option>
                                    <option value="B">Tim B</option>
                                    <option value="C">Tim C</option>
                                    <option value="D">Tim D</option>
                                </select>
                            </div>
                        
                        </div>

                        <!-- Ketik Manual Nama Mesin -->
                        <div class="mb-6">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Nama Mesin / Fasilitas</label>
                            <input type="text" name="machine_name" required placeholder="Contoh: MESIN COMPRESSOR 1, POMPA AIR..." 
                                   class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 uppercase">
                        </div>

                        <!-- Deskripsi Problem -->
                        <div class="mb-8">
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-2">Detail Problem / Temuan</label>
                            <textarea name="problem_description" required rows="4" placeholder="Jelaskan kendala apa yang terjadi, apa yang sudah dilakukan..." 
                                      class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-500 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3"></textarea>
                        </div>

                        <!-- Tombol Aksi -->
                        <div class="flex items-center justify-end gap-3 pt-6 border-t border-[#37415C]/60">
                            <a href="{{ route('handover.index') }}" 
                               class="px-5 py-2.5 rounded-xl bg-[#37415C]/50 hover:bg-[#37415C] text-slate-300 text-xs font-bold transition">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                <span>Simpan & Oper</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>