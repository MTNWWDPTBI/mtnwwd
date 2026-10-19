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

                    <!-- Area Kamera Scanner -->
                    <div class="mb-6">
                        <div id="reader" class="w-full overflow-hidden rounded-xl bg-black border-2 border-dashed border-[#37415C]"></div>
                        <p class="text-[10px] text-slate-400 mt-2 italic">Arahkan kamera ke Barcode. Kode akan otomatis terisi di kolom bawah.</p>
                    </div>

                    <form action="{{ route('tpm.processScan') }}" method="POST" class="text-left" id="scan-form">
                        @csrf
                        
                        <div class="mb-5 relative">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Kode Barcode / Part</label>
                            <input type="text" id="tpm_part_number" name="tpm_part_number" required autofocus placeholder="Ketik manual atau scan..." 
                                   class="w-full bg-[#181A2F] border-2 border-[#FDA481]/50 rounded-xl text-white placeholder-slate-600 focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-4 font-mono text-xl sm:text-2xl text-center shadow-inner tracking-widest">
                             <!-- Tombol reset input manual jika salah -->
                             <button type="button" onclick="document.getElementById('tpm_part_number').value = ''" class="absolute right-4 top-[45px] text-slate-500 hover:text-rose-400 focus:outline-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                             </button>
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Tipe Transaksi</label>
                            <select name="transaction_type" required 
                                    class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl focus:border-[#FDA481] focus:ring focus:ring-[#FDA481]/20 p-3 font-bold text-center appearance-none text-white">
                                <option value="IN" class="text-emerald-400">📥 IN (MASUK / TAMBAH)</option>
                                <option value="OUT" class="text-rose-400">📤 OUT (KELUAR / AMBIL)</option>
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

    <!-- Panggil Library Scanner JS -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Setup Audio Notifikasi (Opsional: Bunyi 'Beep' saat berhasil scan)
            const beepAudio = new Audio('data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU... (Ganti dengan URL file audio beep yang sebenarnya jika diinginkan)'); 
            
            // Variabel untuk mencegah multiple scan beruntun yang cepat
            let lastScanText = "";
            let lastScanTime = 0;

            // Konfigurasi Scanner
            let html5QrcodeScanner = new Html5QrcodeScanner(
                "reader", 
                { 
                    fps: 10, 
                    qrbox: {width: 250, height: 100}, // Dibuat persegi panjang (landscape) lebih cocok untuk barcode
                    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                    rememberLastUsedCamera: true
                },
                /* verbose= */ false
            );

            // Fungsi yang dijalankan saat barcode BERHASIL terbaca
            function onScanSuccess(decodedText, decodedResult) {
                const currentTime = new Date().getTime();
                
                // Mencegah scan barcode yang sama berulang kali dalam 2 detik
                if (decodedText === lastScanText && (currentTime - lastScanTime) < 2000) {
                    return; 
                }

                lastScanText = decodedText;
                lastScanTime = currentTime;

                // Memasukkan hasil scan (decodedText) ke dalam kolom input
                const inputField = document.getElementById('tpm_part_number');
                inputField.value = decodedText;
                
                // Beri efek visual pada input untuk memberitahu user
                inputField.classList.add('ring-4', 'ring-emerald-500/50', 'border-emerald-500');
                setTimeout(() => {
                    inputField.classList.remove('ring-4', 'ring-emerald-500/50', 'border-emerald-500');
                }, 1000);

                 // Opsional: Mainkan suara beep
                 // beepAudio.play().catch(e => console.log('Audio autoplay prevented'));

                // Opsional: Otomatis submit form setelah scan?
                // document.getElementById('scan-form').submit(); // Aktifkan jika ingin langsung ter-submit
            }

            // Mulai Scanner
            html5QrcodeScanner.render(onScanSuccess);
        });
    </script>

    <!-- Sedikit penyesuaian CSS bawaan Library agar cocok dengan tema Gelap -->
    <style>
        #reader { border: none !important; }
        #reader video { border-radius: 12px; }
        #reader button {
            background-color: #181A2F !important;
            color: #FDA481 !important;
            border: 1px solid #37415C !important;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.2s;
        }
        #reader button:hover {
            background-color: #242E49 !important;
        }
        #reader select {
            background-color: #181A2F !important;
            color: white !important;
            border: 1px solid #37415C !important;
            padding: 8px;
            border-radius: 8px;
            margin-bottom: 10px;
            width: 80%;
            max-width: 200px;
        }
        #reader__dashboard_section_csr span { color: #94a3b8 !important; }
        /* Sembunyikan link "powered by" dari library */
        #reader a { display: none !important; }
    </style>
</x-app-layout>