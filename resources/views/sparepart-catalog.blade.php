<x-app-layout>
    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Kotak Filter & Pencarian -->
            <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                <form action="{{ route('sparepart.catalog') }}" method="GET" x-data="filterDropdown()">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        
                        <!-- Filter Pencarian Teks -->
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cari Sparepart</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama part, kode SAP..." class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                        </div>

                        <!-- Filter Mesin -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Mesin</label>
                            <select name="machine_id" x-model="selectedMachine" x-on:change="fetchAssemblies()" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm">
                                <option value="">-- Semua Mesin --</option>
                                <?php foreach($machines as$machine): ?>
                                    <option value="{{ $machine->id }}">{{ $machine->machine_name }}</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Filter Assembly -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Part / Assembly</label>
                            <select name="assembly_id" x-model="selectedAssembly" class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm" x-bind:disabled="assemblies.length === 0">
                                <option value="">-- Semua Assembly --</option>
                                <template x-for="assy in assemblies" :key="assy.id">
                                    <option x-bind:value="assy.id" x-text="assy.assembly_name"></option>
                                </template>
                            </select>
                        </div>

                    </div>
                    
                    <div class="mt-4 flex justify-end space-x-2">
                        <a href="{{ route('sparepart.catalog') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-200">Reset</a>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Terapkan Filter</button>
                    </div>
                </form>
            </div>

            <!-- Daftar Hasil Sparepart -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Part & SAP</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Digunakan Di (Mesin & Assy)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lokasi Bin</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if(count($spareparts) > 0): ?>
                            <?php foreach($spareparts as$part): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-gray-900">{{ $part->part_name }}</div>
                                    <div class="text-sm text-gray-500">SAP: {{ $part->sap_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php foreach($part->assemblies as$assy): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                            {{ $assy->machine->machine_name }} - {{$assy->assembly_name }}
                                            (Butuh {{ $assy->pivot->qty }} {{$assy->pivot->unit }})
                                        </span><br>
                                    <?php endforeach; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ $part->inventory->bin_location ?? 'Belum ada lokasi' }}
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-center text-gray-500">Tidak ada data sparepart yang sesuai dengan filter.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div class="px-6 py-4">
                    {{ $spareparts->links() }}
                </div>
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('filterDropdown', () => ({
                selectedMachine: '{{ request('machine_id') }}',
                selectedAssembly: '{{ request('assembly_id') }}',
                assemblies: [],
                
                init() {
                    if (this.selectedMachine) {
                        this.fetchAssemblies(this.selectedAssembly);
                    }
                },
                
                fetchAssemblies(oldAssemblyId = '') {
                    this.assemblies = [];
                    this.selectedAssembly = ''; 
                    
                    if (!this.selectedMachine) return;

                    fetch(`/api/assemblies/${this.selectedMachine}`)
                        .then(res => res.json())
                        .then(data => {
                            this.assemblies = data;
                            if(oldAssemblyId) {
                                this.selectedAssembly = oldAssemblyId;
                            }
                        });
                }
            }))
        })
    </script>
</x-app-layout>