<x-app-layout>
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4">Tambah Sparepart Baru</h2>
                <form action="{{ route('spareparts.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kode SAP</label>
                        <input type="text" name="sap_code" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Sparepart (Part Name)</label>
                        <input type="text" name="part_name" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Lokasi Rak / Bin</label>
                        <input type="text" name="bin_location" placeholder="Misal: RAK-A1" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Digunakan pada Assembly</label>
                        <select name="assembly_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            <option value="">-- Pilih Assembly / Mesin --</option>
                            @foreach($assemblies as $assy)
                                <option value="{{ $assy->id }}">{{ $assy->machine->machine_name ?? '' }} ➔ {{ $assy->assembly_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Kebutuhan (Qty)</label>
                            <input type="number" name="qty" value="1" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Satuan (Unit)</label>
                            <input type="text" name="unit" value="Pcs" class="w-full border-gray-300 rounded-md shadow-sm" required>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('spareparts.index') }}" class="bg-gray-300 px-4 py-2 rounded text-gray-800">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>