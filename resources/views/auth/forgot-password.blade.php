<x-guest-layout>
    <div class="bg-white/95 dark:bg-[#242E49] backdrop-blur-xl border border-slate-200 dark:border-[#37415C]/80 rounded-2xl sm:rounded-3xl p-6 sm:p-9 shadow-2xl shadow-slate-200/50 dark:shadow-black/70 relative overflow-hidden transition-colors duration-200">
        
        <!-- Top Accent Gradient Line -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#B4182D] via-[#54162B] to-[#FDA481]"></div>

        <div class="mb-6 text-center">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Lupa Kata Sandi?</h2>
            <p class="text-xs text-slate-500 dark:text-slate-300 mt-2 leading-relaxed">
                Masukkan alamat email yang terdaftar pada akun Anda. Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
            </p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-1.5">
                    Alamat Email
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.206"/>
                        </svg>
                    </div>
                    <input id="email" 
                           type="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required 
                           autofocus 
                           placeholder="nama@bekaert.com"
                           class="w-full bg-[#f8fafc] dark:bg-[#181A2F] border @error('email') border-rose-500 dark:border-rose-500 @else border-slate-300 dark:border-[#37415C] @enderror rounded-xl pl-11 pr-4 py-2.5 sm:py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400 focus:outline-none focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 transition shadow-inner font-medium">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2 flex flex-col gap-3">
                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2 py-3.5 px-5 rounded-xl bg-gradient-to-r from-[#B4182D] via-[#7A1525] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] text-white text-xs sm:text-sm font-bold tracking-wider uppercase shadow-lg shadow-[#B4182D]/30 transition duration-200">
                    <span>Kirim Tautan Reset</span>
                </button>

                <a href="{{ route('login') }}" 
                   class="text-center text-xs font-semibold text-slate-500 dark:text-[#FDA481] hover:underline transition">
                    &larr; Kembali ke Halaman Masuk
                </a>
            </div>
        </form>

    </div>
</x-guest-layout>
