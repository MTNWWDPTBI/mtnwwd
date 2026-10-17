<x-app-layout>
    <div class="space-y-6 pb-12 flex justify-center items-center min-h-[80vh]">
        <div class="max-w-md w-full">
            
            <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
                <div class="p-6 sm:p-8 text-center">
                    <h2 class="text-2xl font-extrabold text-[#FDA481] mb-6 flex items-center justify-center gap-2">
                        <span>📷 SCAN BARCODE TPM</span>
                    </h2>

                    @if(session('success'))
                        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-4 py-3 rounded-xl mb-6 text-left font-semibold text-sm">
                            {!! session('success') !!}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-4 py-3 rounded-xl mb-6 text-left font-bold text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{ route('tpm.processScan') }}" method="POST" class="text-left">
                        @csrf
                        
                        <div class="mb-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Scan Barcode / Ketik Kode</label>
                            <input type="text" name="tpm_part_number" required autofocus placeholder="Scan disini..." 
                                   class="w-full bg-[#181A2F] border-2 border-[#FDA481]/50 rounded-xl text-white placeholder-slate-600 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-4 font-mono text-2xl text-center shadow-inner tracking-widest">
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipe Transaksi</label>
                            <select name="transaction_type" required 
                                    class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 font-bold text-center appearance-none">
                                <option value="IN" class="text-emerald-400 bg-[#181A2F]">📥 IN (MASUK / TAMBAH)</option>
                                <option value="OUT" class="text-rose-400 bg-[#181A2F]">📤 OUT (KELUAR / AMBIL)</option>
                            </select>
                        </div>

                        <div class="mb-8">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Jumlah Qty</label>
                            <input type="number" name="qty" min="1" value="1" required 
                                   class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 text-center text-xl font-bold">
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white px-4 py-4 rounded-xl font-bold text-lg tracking-widest shadow-md transition duration-200">
                            EKSEKUSI STOK
                        </button>
                        
                        <div class="mt-6 text-center">
                            <a href="{{ route('tpm.index') }}" class="text-xs text-slate-500 hover:text-[#FDA481] transition flex items-center justify-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                                Kembali ke Daftar TPM
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>