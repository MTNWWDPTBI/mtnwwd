<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Data Master Assembly</h2>
                <a href="{{ route('assemblies.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Assembly</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">{{ $errors->first() }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mesin Induk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Assembly</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Assembly</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($assemblies as $assy)
                        <tr>
                            <td class="px-6 py-4 text-gray-700"><span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">{{ $assy->machine->machine_name }}</span></td>
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $assy->assembly_code }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $assy->assembly_name }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('assemblies.edit', $assy->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('assemblies.destroy', $assy->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin hapus assembly ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $assemblies->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>