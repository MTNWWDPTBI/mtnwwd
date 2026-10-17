<x-app-layout>
    <div class="space-y-4 sm:space-y-6 pb-8 sm:pb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div>
                <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight flex items-center gap-2 sm:gap-3">
                    <span>Manajemen Akun</span>
                    <span class="text-[10px] sm:text-xs font-semibold px-2 py-0.5 sm:px-3 sm:py-1 rounded-full bg-[#242E49] text-[#FDA481] border border-[#FDA481]/30">
                        Admin Only
                    </span>
                </h1>
                <p class="text-[10px] sm:text-sm text-slate-400 mt-1">Kelola akses masuk untuk Admin dan Tim Teknisi.</p>
            </div>
            <div>
                <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 sm:gap-2 px-4 py-2 sm:px-5 sm:py-2.5 rounded-lg sm:rounded-xl bg-gradient-to-r from-[#B4182D] to-[#54162B] text-white text-[10px] sm:text-xs font-bold shadow-md transition duration-200">
                    <span>+ Daftarkan Teknisi</span>
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 px-3 py-2 sm:px-4 sm:py-3 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-semibold">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-rose-500/10 border border-rose-500/30 text-rose-400 px-3 py-2 sm:px-4 sm:py-3 rounded-xl sm:rounded-2xl text-xs sm:text-sm font-bold">{{ session('error') }}</div>
        @endif

        <div class="bg-[#242E49] rounded-xl sm:rounded-[24px] border border-[#37415C]/70 shadow-m3-2 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-[#37415C]/60 text-left">
                    <thead class="bg-[#181A2F]/90">
                        <tr>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase text-[#FDA481] whitespace-nowrap">Nama & Email</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase text-[#FDA481] text-center whitespace-nowrap">Akses / Role</th>
                            <th class="px-3 py-3 sm:px-6 sm:py-4 text-[10px] sm:text-xs font-bold uppercase text-[#FDA481] text-right whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#37415C]/40 text-xs sm:text-sm">
                        @foreach($users as $user)
                        <tr class="hover:bg-[#181A2F]/40 transition">
                            <td class="px-3 py-3 sm:px-6 sm:py-4 whitespace-nowrap">
                                <div class="font-bold text-white">{{ $user->name }}</div>
                                <div class="text-[10px] sm:text-xs text-slate-400 mt-1">{{ $user->email }}</div>
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-center whitespace-nowrap">
                                @if($user->role === 'admin')
                                    <span class="inline-block px-2 py-1 rounded bg-rose-500/20 text-rose-400 border border-rose-500/30 font-bold text-[10px] sm:text-xs">ADMIN</span>
                                @else
                                    <span class="inline-block px-2 py-1 rounded bg-blue-500/20 text-blue-400 border border-blue-500/30 font-bold text-[10px] sm:text-xs">TEKNISI</span>
                                @endif
                            </td>
                            <td class="px-3 py-3 sm:px-6 sm:py-4 text-right whitespace-nowrap">
                                @if(auth()->id() !== $user->id)
                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Hapus akun ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-rose-400 hover:text-rose-300 font-semibold text-[10px] sm:text-xs transition">Hapus</button>
                                    </form>
                                @else
                                    <span class="text-slate-500 italic text-[10px] sm:text-xs">Akun Kamu</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>