<x-guest-layout>
    <div class="bg-white/95 dark:bg-[#242E49] backdrop-blur-xl border border-slate-200 dark:border-[#37415C]/80 rounded-2xl sm:rounded-3xl p-6 sm:p-9 shadow-2xl shadow-slate-200/50 dark:shadow-black/70 relative overflow-hidden transition-colors duration-200">
        
        <!-- Top Accent Gradient Line (Matching Dashboard Material Design) -->
        <div class="absolute top-0 inset-x-0 h-1.5 bg-gradient-to-r from-[#B4182D] via-[#54162B] to-[#FDA481]"></div>

        <!-- 1. Header Branding & Logo Industrial (Matching Navigation Drawer) -->
        <div class="flex flex-col items-center text-center mb-7">
            <!-- Industrial Emblem Badge -->
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-[#B4182D] via-[#54162B] to-[#FDA481] p-0.5 shadow-xl shadow-[#B4182D]/25 mb-3.5 group hover:scale-105 transition-transform duration-300">
                <div class="w-full h-full rounded-[14px] bg-[#181A2F] flex items-center justify-center">
                    <svg class="w-8 h-8 text-[#FDA481] transition-transform duration-700 group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
            </div>
            
            <!-- Title with Dashboard Gradient -->
            <h1 class="text-3xl font-black tracking-wider text-transparent bg-clip-text bg-gradient-to-r from-[#FDA481] to-[#B4182D]">
                MTN WWD
            </h1>
            
            <!-- Bekaert Indonesia Pill Badge -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1 mt-1.5 rounded-full bg-[#FDA481]/15 border border-[#FDA481]/35 text-[#FDA481] text-xs font-bold tracking-wide">
                <span class="w-1.5 h-1.5 rounded-full bg-[#FDA481] animate-ping"></span>
                <span>BEKAERT INDONESIA</span>
            </div>
            
            <p class="text-xs text-slate-500 dark:text-slate-300 mt-2 font-medium">
                Maintenance & Reliability Portal
            </p>
        </div>

        <!-- 2. Session Status Flash -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <!-- 3. Form Login -->
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <!-- Email Address / Username -->
            <div>
                <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-1.5">
                    Email / Username
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
                           autocomplete="username" 
                           placeholder="nama@bekaert.com"
                           class="w-full bg-[#f8fafc] dark:bg-[#181A2F] border @error('email') border-rose-500 dark:border-rose-500 @else border-slate-300 dark:border-[#37415C] @enderror rounded-xl pl-11 pr-4 py-2.5 sm:py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400 focus:outline-none focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 transition shadow-inner font-medium">
                </div>
                @error('email')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Password with Show/Hide Toggle -->
            <div>
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-200 mb-1.5">
                    Kata Sandi
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 dark:text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input id="password" 
                           type="password" 
                           name="password" 
                           required 
                           autocomplete="current-password" 
                           placeholder="••••••••"
                           class="w-full bg-[#f8fafc] dark:bg-[#181A2F] border @error('password') border-rose-500 dark:border-rose-500 @else border-slate-300 dark:border-[#37415C] @enderror rounded-xl pl-11 pr-11 py-2.5 sm:py-3 text-sm text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-400 focus:outline-none focus:border-[#FDA481] focus:ring-2 focus:ring-[#FDA481]/30 transition shadow-inner font-medium">
                    
                    <!-- Show/Hide Toggle Button -->
                    <button type="button" 
                            onclick="togglePasswordVisibility()" 
                            title="Tampilkan / Sembunyikan Kata Sandi"
                            class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-[#FDA481] dark:hover:text-[#FDA481] transition focus:outline-none">
                        <!-- Eye Icon -->
                        <svg id="eyeIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <!-- Eye Slash Icon (Hidden by default) -->
                        <svg id="eyeSlashIcon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs text-rose-500 dark:text-rose-400 font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between pt-1">
                <label for="remember_me" class="inline-flex items-center cursor-pointer select-none">
                    <input id="remember_me" 
                           type="checkbox" 
                           name="remember"
                           class="w-4 h-4 rounded border-slate-300 dark:border-[#37415C] bg-[#f8fafc] dark:bg-[#181A2F] text-[#B4182D] focus:ring-[#FDA481] focus:ring-offset-0 cursor-pointer">
                    <span class="ml-2 text-xs text-slate-700 dark:text-slate-300 font-medium">Ingat Saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" 
                       class="text-xs font-semibold text-[#B4182D] dark:text-[#FDA481] hover:underline transition">
                        Lupa kata sandi?
                    </a>
                @endif
            </div>

            <!-- Submit Button (MASUK KE SISTEM - Dashboard Red/Crimson Palette) -->
            <div class="pt-2">
                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2.5 py-3.5 px-5 rounded-xl bg-gradient-to-r from-[#B4182D] via-[#7A1525] to-[#54162B] hover:from-[#B4182D] hover:to-[#B4182D] active:scale-[0.99] text-white text-xs sm:text-sm font-bold tracking-wider uppercase shadow-lg shadow-[#B4182D]/30 hover:shadow-[#B4182D]/50 transition duration-200">
                    <span>MASUK KE SISTEM</span>
                    <svg class="w-4 h-4 text-[#FDA481]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </button>
            </div>
        </form>

        <!-- Security & Industrial Portal Note -->
        <div class="mt-6 pt-5 border-t border-slate-100 dark:border-[#37415C]/60 flex items-center justify-center gap-2 text-[11px] text-slate-500 dark:text-slate-400 font-medium">
            <svg class="w-3.5 h-3.5 text-[#FDA481] shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.35-.166-2.001A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"/>
            </svg>
            <span>Akses Terenkripsi &bull; Khusus Personel PT Bekaert</span>
        </div>

    </div>

    <!-- Password Visibility Toggle Script -->
    <script>
        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            const eyeSlashIcon = document.getElementById('eyeSlashIcon');
            
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                pwdInput.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }
    </script>
</x-guest-layout>
