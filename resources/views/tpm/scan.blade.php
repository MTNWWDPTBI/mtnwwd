<x-app-layout>
    <div class="space-y-6 pb-12 flex justify-center items-center min-h-[85vh]">
        <div class="max-w-xl w-full">
            
            <div class="bg-[#242E49] rounded-2xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
                <div class="p-5 sm:p-8">
                    
                    <!-- Header -->
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#181A2F] border border-[#FDA481]/30 text-[#FDA481] text-xs font-semibold mb-2">
                            <span>⚡ Eksekusi Stok TPM</span>
                        </div>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">
                            Tambah / Ambil Stok Barang
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Pilih manual via lokasi & nama part, atau gunakan scanner barcode.
                        </p>
                    </div>

                    @if(session('success'))
                        <div class="bg-emerald-500/15 border border-emerald-500/40 text-emerald-300 px-4 py-3 rounded-xl mb-6 text-left font-medium text-xs sm:text-sm flex items-start gap-2">
                            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <div>{!! session('success') !!}</div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-rose-500/15 border border-rose-500/40 text-rose-300 px-4 py-3 rounded-xl mb-6 text-left font-medium text-xs sm:text-sm flex items-start gap-2">
                            <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <div>{{ session('error') }}</div>
                        </div>
                    @endif

                    <!-- Tab Switcher Mode -->
                    <div class="grid grid-cols-2 gap-2 p-1 bg-[#181A2F] rounded-xl border border-[#37415C]/60 mb-6 text-xs font-bold">
                        <button type="button" id="tab-manual-btn" onclick="switchMode('manual')" 
                                class="py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 bg-[#FDA481] text-[#181A2F] shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span>Pilih Manual (Lokasi)</span>
                        </button>
                        <button type="button" id="tab-camera-btn" onclick="switchMode('camera')" 
                                class="py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 text-slate-300 hover:text-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Scan Kamera</span>
                        </button>
                    </div>

                    <!-- Mode 1: Pilih Manual (Lokasi & Part) -->
                    <div id="section-manual" class="space-y-4 mb-6">
                        <!-- Step 1: Pilih Lokasi -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-1.5">
                                1. Pilih Lokasi Rak
                            </label>
                            <select id="select-location" onchange="onLocationSelected()" 
                                    class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm p-3 focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481]">
                                <option value="">-- Pilih Lokasi --</option>
                                <option value="__ALL__">🌐 Tampilkan Semua Lokasi ({{ $parts->count() }} Part)</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc }}" {{ $preselectedLocation === $loc ? 'selected' : '' }}>
                                        📍 Lokasi {{ $loc }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Step 2: Pilih Part -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-[#FDA481] mb-1.5">
                                2. Pilih Nama Barang / Part
                            </label>
                            <select id="select-part" onchange="onPartSelected()" 
                                    class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white text-sm p-3 focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481]">
                                <option value="">-- Pilih Part --</option>
                            </select>
                        </div>
                    </div>

                    <!-- Mode 2: Area Kamera Scanner Barcode -->
                    <div id="section-camera" class="mb-6 hidden">
                        <div id="reader" class="w-full overflow-hidden rounded-xl bg-black border-2 border-dashed border-[#37415C]"></div>
                        <p class="text-[11px] text-slate-400 mt-2 text-center italic">
                            Arahkan kamera ke Barcode TPM. Data barang akan langsung terdeteksi.
                        </p>
                    </div>

                    <!-- Input Barcode Cadangan / Manual Typing -->
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">
                                Kode Barcode / Part Number
                            </label>
                            <span class="text-[10px] text-slate-500">Bisa diketik manual</span>
                        </div>
                        <input type="text" id="tpm_part_number_display" placeholder="Scan atau ketik kode barcode..."
                               class="w-full bg-[#181A2F] border border-[#37415C] rounded-xl text-white placeholder-slate-600 focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481] p-3 font-mono text-center tracking-wider text-sm sm:text-base font-bold"
                               oninput="onBarcodeTyped(this.value)">
                    </div>

                    <!-- Card Info Part Terpilih -->
                    <div id="part-info-card" class="mb-6 hidden bg-[#181A2F] border-2 border-[#FDA481]/50 rounded-2xl p-4 sm:p-5 relative shadow-inner">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                                    Part Terpilih
                                </span>
                                <h3 id="card-part-name" class="text-base sm:text-lg font-extrabold text-white mt-1"></h3>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Barcode: <span id="card-part-number" class="font-mono text-white font-bold"></span> | 
                                    Lokasi: <span id="card-part-location" class="text-[#FDA481] font-semibold"></span>
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span class="text-[10px] text-slate-400 uppercase block">Stok Sekarang</span>
                                <span id="card-part-stock" class="text-2xl font-black text-white">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Form Eksekusi Stok -->
                    <form action="{{ route('tpm.processScan') }}" method="POST" id="scan-form" class="space-y-5">
                        @csrf
                        <input type="hidden" id="form_part_id" name="part_id" value="">
                        <input type="hidden" id="form_part_number" name="tpm_part_number" value="">

                        <!-- Tipe Transaksi (IN / OUT) -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                Tipe Transaksi
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="cursor-pointer">
                                    <input type="radio" name="transaction_type" value="IN" class="peer sr-only" checked onchange="updateTypeColor()">
                                    <div class="p-3.5 rounded-xl border border-[#37415C] bg-[#181A2F] text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-500/20 peer-checked:text-emerald-300 hover:border-slate-500">
                                        <div class="text-base font-extrabold flex items-center justify-center gap-1.5">
                                            <span>📥</span>
                                            <span>IN (TAMBAH)</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">Barang masuk ke rak</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="transaction_type" value="OUT" class="peer sr-only" onchange="updateTypeColor()">
                                    <div class="p-3.5 rounded-xl border border-[#37415C] bg-[#181A2F] text-center transition peer-checked:border-rose-500 peer-checked:bg-rose-500/20 peer-checked:text-rose-300 hover:border-slate-500">
                                        <div class="text-base font-extrabold flex items-center justify-center gap-1.5">
                                            <span>📤</span>
                                            <span>OUT (AMBIL)</span>
                                        </div>
                                        <span class="text-[10px] text-slate-400 block mt-0.5">Barang diambil / dipakai</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Jumlah Qty -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">
                                Jumlah (Qty)
                            </label>
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="adjustQty(-1)" 
                                        class="w-12 h-12 rounded-xl bg-[#181A2F] border border-[#37415C] hover:bg-[#37415C] text-white text-xl font-bold flex items-center justify-center transition shrink-0">
                                    -
                                </button>
                                <input type="number" id="qty_input" name="qty" min="1" value="1" required 
                                       class="flex-1 bg-[#181A2F] border border-[#37415C] rounded-xl text-white focus:border-[#FDA481] focus:ring-1 focus:ring-[#FDA481] p-3 text-center text-xl font-extrabold">
                                <button type="button" onclick="adjustQty(1)" 
                                        class="w-12 h-12 rounded-xl bg-[#181A2F] border border-[#37415C] hover:bg-[#37415C] text-white text-xl font-bold flex items-center justify-center transition shrink-0">
                                    +
                                </button>
                            </div>

                            <!-- Tombol Cepat Tambah Qty -->
                            <div class="flex items-center justify-center gap-2 mt-2">
                                <button type="button" onclick="setQty(1)" class="px-2.5 py-1 rounded bg-[#181A2F] border border-[#37415C] text-slate-300 hover:text-white text-[11px] font-semibold transition">1</button>
                                <button type="button" onclick="setQty(5)" class="px-2.5 py-1 rounded bg-[#181A2F] border border-[#37415C] text-slate-300 hover:text-white text-[11px] font-semibold transition">5</button>
                                <button type="button" onclick="setQty(10)" class="px-2.5 py-1 rounded bg-[#181A2F] border border-[#37415C] text-slate-300 hover:text-white text-[11px] font-semibold transition">10</button>
                                <button type="button" onclick="setQty(20)" class="px-2.5 py-1 rounded bg-[#181A2F] border border-[#37415C] text-slate-300 hover:text-white text-[11px] font-semibold transition">20</button>
                            </div>
                        </div>

                        <!-- Tombol Submit Eksekusi -->
                        <button type="submit" id="submit-btn" 
                                class="w-full bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white px-5 py-4 rounded-xl font-extrabold text-base sm:text-lg tracking-wider shadow-lg transition duration-200 flex items-center justify-center gap-2">
                            <span>⚡ EKSEKUSI STOK</span>
                        </button>
                    </form>

                    <!-- Navigasi Kembali -->
                    <div class="mt-6 text-center">
                        <a href="{{ route('tpm.index') }}" class="text-xs text-slate-400 hover:text-[#FDA481] transition inline-flex items-center gap-1 font-semibold">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Kembali ke Daftar Master TPM</span>
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <!-- Library Scanner JS -->
    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

    <script>
        // Data Semua Part dari Database
        const allParts = @json($parts);
        const preselectedPartNumber = @json($preselectedPartNumber);
        const preselectedLocation = @json($preselectedLocation);

        let scannerInstance = null;
        let isScannerRunning = false;

        document.addEventListener('DOMContentLoaded', function() {
            // Jika ada preselected location atau part_number
            if (preselectedLocation) {
                document.getElementById('select-location').value = preselectedLocation;
                onLocationSelected();
            } else {
                onLocationSelected();
            }

            if (preselectedPartNumber) {
                selectPartByNumber(preselectedPartNumber);
            }
        });

        // Ganti Mode antara Pilih Manual vs Kamera Scan
        function switchMode(mode) {
            const tabManualBtn = document.getElementById('tab-manual-btn');
            const tabCameraBtn = document.getElementById('tab-camera-btn');
            const sectionManual = document.getElementById('section-manual');
            const sectionCamera = document.getElementById('section-camera');

            if (mode === 'manual') {
                tabManualBtn.className = 'py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 bg-[#FDA481] text-[#181A2F] shadow-sm';
                tabCameraBtn.className = 'py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 text-slate-300 hover:text-white';
                sectionManual.classList.remove('hidden');
                sectionCamera.classList.add('hidden');
                stopScanner();
            } else {
                tabCameraBtn.className = 'py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 bg-[#FDA481] text-[#181A2F] shadow-sm';
                tabManualBtn.className = 'py-2.5 px-3 rounded-lg transition duration-150 flex items-center justify-center gap-1.5 text-slate-300 hover:text-white';
                sectionCamera.classList.remove('hidden');
                sectionManual.classList.add('hidden');
                startScanner();
            }
        }

        // Jalankan scanner kamera
        function startScanner() {
            if (isScannerRunning) return;

            try {
                scannerInstance = new Html5QrcodeScanner(
                    "reader", 
                    { 
                        fps: 10, 
                        qrbox: { width: 250, height: 120 },
                        supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
                        rememberLastUsedCamera: true
                    },
                    /* verbose= */ false
                );

                scannerInstance.render(onScanSuccess);
                isScannerRunning = true;
            } catch (e) {
                console.error("Gagal memulai kamera scanner:", e);
            }
        }

        function stopScanner() {
            if (scannerInstance && isScannerRunning) {
                try {
                    scannerInstance.clear();
                } catch(e) {}
                isScannerRunning = false;
            }
        }

        // Handler saat scan berhasil
        function onScanSuccess(decodedText) {
            if (!decodedText) return;
            selectPartByNumber(decodedText.trim());

            // Getar HP (haptic feedback) jika didukung
            if (navigator.vibrate) {
                navigator.vibrate(100);
            }

            // Scroll ke tombol submit agar cepat
            document.getElementById('part-info-card').scrollIntoView({ behavior: 'smooth' });
        }

        // Filter daftar part saat lokasi dipilih
        function onLocationSelected() {
            const locSelect = document.getElementById('select-location');
            const partSelect = document.getElementById('select-part');
            const selectedLoc = locSelect.value;

            partSelect.innerHTML = '<option value="">-- Pilih Part --</option>';

            let filteredParts = allParts;
            if (selectedLoc && selectedLoc !== '__ALL__') {
                filteredParts = allParts.filter(p => p.location === selectedLoc);
            }

            filteredParts.forEach(part => {
                const opt = document.createElement('option');
                opt.value = part.id;
                opt.textContent = `${part.name} (${part.tpm_part_number}) - Stok: ${part.stock}`;
                opt.dataset.partNumber = part.tpm_part_number;
                opt.dataset.name = part.name;
                opt.dataset.location = part.location;
                opt.dataset.stock = part.stock;
                opt.dataset.minStock = part.min_stock;
                partSelect.appendChild(opt);
            });
        }

        // Handler saat part dipilih dari dropdown
        function onPartSelected() {
            const partSelect = document.getElementById('select-part');
            const partId = parseInt(partSelect.value);

            if (!partId) {
                hidePartCard();
                return;
            }

            const part = allParts.find(p => p.id === partId);
            if (part) {
                applyPartSelection(part);
            }
        }

        // Cari & pilih part berdasarkan kode barcode
        function selectPartByNumber(partNumber) {
            const part = allParts.find(p => p.tpm_part_number.toUpperCase() === partNumber.toUpperCase());
            if (part) {
                document.getElementById('select-location').value = part.location;
                onLocationSelected();
                document.getElementById('select-part').value = part.id;
                applyPartSelection(part);
            } else {
                // Jika tidak ditemukan di master, tetap isi input barcode display
                document.getElementById('tpm_part_number_display').value = partNumber;
                document.getElementById('form_part_number').value = partNumber;
                document.getElementById('form_part_id').value = '';
                hidePartCard();
            }
        }

        function onBarcodeTyped(value) {
            const trimmed = value.trim();
            if (trimmed.length >= 3) {
                selectPartByNumber(trimmed);
            }
        }

        // Tampilkan info part terpilih & isi hidden inputs
        function applyPartSelection(part) {
            document.getElementById('form_part_id').value = part.id;
            document.getElementById('form_part_number').value = part.tpm_part_number;
            document.getElementById('tpm_part_number_display').value = part.tpm_part_number;

            document.getElementById('card-part-name').textContent = part.name;
            document.getElementById('card-part-number').textContent = part.tpm_part_number;
            document.getElementById('card-part-location').textContent = part.location;

            const stockEl = document.getElementById('card-part-stock');
            stockEl.textContent = part.stock;
            if (part.stock <= part.min_stock) {
                stockEl.className = 'text-2xl font-black text-rose-400';
            } else {
                stockEl.className = 'text-2xl font-black text-emerald-400';
            }

            document.getElementById('part-info-card').classList.remove('hidden');
        }

        function hidePartCard() {
            document.getElementById('part-info-card').classList.add('hidden');
            document.getElementById('form_part_id').value = '';
            document.getElementById('form_part_number').value = '';
        }

        function adjustQty(amount) {
            const qtyInput = document.getElementById('qty_input');
            let current = parseInt(qtyInput.value) || 1;
            let nextVal = current + amount;
            if (nextVal < 1) nextVal = 1;
            qtyInput.value = nextVal;
        }

        function setQty(val) {
            document.getElementById('qty_input').value = val;
        }

        function updateTypeColor() {
            // Handled via Tailwind peer-checked
        }
    </script>

    <!-- Penyesuaian styling reader library -->
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
            margin: 8px 0;
            cursor: pointer;
        }
        #reader select {
            background-color: #181A2F !important;
            color: white !important;
            border: 1px solid #37415C !important;
            padding: 6px 10px;
            border-radius: 8px;
        }
        #reader a { display: none !important; }
    </style>
</x-app-layout>
