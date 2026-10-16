<x-app-layout>
    <div class="py-8">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold mb-4">Edit Assembly</h2>
                <form action="{{ route('assemblies.update', $assembly->id) }}" method="POST">
                    @csrf @method('PUT')
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Mesin Induk</label>
                        <select name="machine_id" class="w-full border-gray-300 rounded-md shadow-sm" required>
                            @foreach($machines as $machine)
                                <option value="{{ $machine->id }}" {{ $machine->id == $assembly->machine_id ? 'selected' : '' }}>
                                    {{ $machine->machine_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Kode Assembly</label>
                        <input type="text" name="assembly_code" value="{{ $assembly->assembly_code }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nama Assembly</label>
                        <input type="text" name="assembly_name" value="{{ $assembly->assembly_name }}" class="w-full border-gray-300 rounded-md shadow-sm" required>
                    </div>
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('assemblies.index') }}" class="bg-gray-300 px-4 py-2 rounded text-gray-800">Batal</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>