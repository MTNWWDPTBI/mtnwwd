<x-app-layout>
    <div class="max-w-xl mx-auto space-y-6 pb-12">
        <div>
            <h1 class="text-2xl font-extrabold text-white tracking-tight">Edit Assembly</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-1">Perbarui relasi mesin induk atau nama sub-assembly.</p>
        </div>

        <div class="bg-[#242E49] p-6 sm:p-8 rounded-[24px] border border-[#37415C]/70 shadow-m3-2">
            <form action="{{ route('assemblies.update', $assembly->id) }}" method="POST" class="space-y-5">
                @csrf @method('PUT')
                
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Mesin Induk</label>
                    <select name="machine_id" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                        @foreach($machines as $machine)
                            <option value="{{ $machine->id }}" {{ $machine->id == $assembly->machine_id ? 'selected' : '' }}>
                                {{ $machine->machine_name }} ({{ $machine->machine_code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Kode Assembly</label>
                    <input type="text" name="assembly_code" value="{{ $assembly->assembly_code }}" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">Nama Assembly</label>
                    <input type="text" name="assembly_name" value="{{ $assembly->assembly_name }}" class="w-full py-2.5 px-4 bg-[#181A2F] border border-[#37415C] text-white focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 rounded-xl text-sm" required>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-[#37415C]/50">
                    <a href="{{ route('assemblies.index') }}" class="px-5 py-2.5 rounded-xl bg-[#181A2F] text-slate-300 hover:text-white text-xs font-semibold border border-[#37415C] transition">Batal</a>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs font-bold tracking-wide shadow-md transition duration-200">Perbarui Assembly</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>