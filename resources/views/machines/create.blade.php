<x-app-layout>
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4">Tambah Mesin Baru</h2>
                <form action="{{ route('machines.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kode Mesin (Misal: MC006)</label>
                        <input type="text" name="machine_code" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Mesin</label>
                        <input type="text" name="machine_name" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('machines.index') }}" class="bg-gray-300 px-4 py-2 rounded text-gray-800">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>