<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-3">
                <span>Import Master Data (BOM & Suku Cadang)</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">
                Unggah berkas Excel (.xlsx / .xls) untuk sinkronisasi otomatis mesin, assembly, sparepart, dan relasi BOM.
            </p>
        </div>

        @if (session('success'))
            <div class="bg-[#242E49] border border-emerald-500/40 text-emerald-300 px-5 py-4 rounded-2xl flex items-center gap-3 shadow-sm">
                <svg class="w-6 h-6 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-[#242E49] rounded-[24px] border border-[#37415C]/70 shadow-m3-2 p-6 sm:p-8">
            <form action="{{ route('import.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2" for="file_excel">
                        Pilih Berkas Excel (.xlsx, .xls)
                    </label>
                    <div class="relative border-2 border-dashed border-[#37415C] hover:border-[#FDA481]/60 rounded-2xl p-6 text-center bg-[#181A2F]/80 transition">
                        <svg class="w-10 h-10 mx-auto text-[#FDA481] mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <input type="file" 
                               name="file_excel" 
                               id="file_excel" 
                               accept=".xlsx, .xls" 
                               class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#FDA481]/20 file:text-[#FDA481] hover:file:bg-[#FDA481]/30 cursor-pointer" 
                               required>
                        <p class="text-[11px] text-slate-500 mt-2">Mendukung format multi-sheet: Machine_Master, Assembly_Master, Sparepart_Master, Assembly_BOM.</p>
                    </div>
                    @error('file_excel')
                        <p class="text-rose-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#37415C]/50">
                    <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-xl bg-[#181A2F] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">
                        Upload & Proses Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>