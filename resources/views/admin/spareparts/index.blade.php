<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold text-gray-800">Manajemen Master Sparepart</h2>
                <a href="{{ route('spareparts.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tambah Sparepart</a>
            </div>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Part Name & SAP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lokasi Bin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Digunakan Pada Assembly</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($spareparts as $part)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900">{{ $part->part_name }}</div>
                                <div class="text-xs text-gray-500">SAP: {{ $part->sap_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">{{ $part->inventory->bin_location ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @foreach($part->assemblies as $assy)
                                    <span class="bg-blue-50 text-blue-700 border border-blue-200 px-2 py-0.5 rounded text-xs">
                                        {{ $assy->machine->machine_name ?? '' }} - {{ $assy->assembly_name }} ({{ $assy->pivot->qty }} {{ $assy->pivot->unit }})
                                    </span>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-right whitespace-nowrap">
                                <a href="{{ route('spareparts.edit', $part->id) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                <form action="{{ route('spareparts.destroy', $part->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus sparepart ini?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="p-4">{{ $spareparts->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>