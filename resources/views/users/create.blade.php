<x-app-layout>
    <div class="space-y-6 pb-12">
        <div class="max-w-xl mx-auto bg-[#242E49] p-6 sm:p-8 rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <h2 class="text-xl sm:text-2xl font-extrabold text-white mb-6">Daftarkan Akun Baru</h2>
            
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#FDA481] mb-2">Nama Pengguna (Contoh: Tim B, atau Nama Asli)</label>
                        <input type="text" name="name" required class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white p-3 text-sm focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#FDA481] mb-2">Email (Untuk Login)</label>
                        <input type="email" name="email" required class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white p-3 text-sm focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#FDA481] mb-2">Level Akses (Role)</label>
                        <select name="role" required class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white p-3 text-sm focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20">
                            <option value="teknisi">TEKNISI (Hanya Scan & Input Logbook)</option>
                            <option value="admin">ADMIN (Akses Penuh)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#FDA481] mb-2">Password</label>
                            <input type="password" name="password" required class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white p-3 text-sm focus:border-[#FDA481]">
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-[#FDA481] mb-2">Ulangi Password</label>
                            <input type="password" name="password_confirmation" required class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white p-3 text-sm focus:border-[#FDA481]">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="px-5 py-2.5 rounded-xl bg-[#37415C]/50 hover:bg-[#37415C] text-slate-300 text-xs font-bold transition">Batal</a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] text-white text-xs font-bold shadow-md transition">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>