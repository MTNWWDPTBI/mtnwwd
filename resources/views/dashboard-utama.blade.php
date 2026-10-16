<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard WWD') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-bold mb-4">Selamat datang di Maintenance WWD!</h3>
                    <p>Ini adalah halaman utama aplikasi. Silakan klik menu <strong>Sparepart</strong> di atas untuk masuk ke Katalog Sparepart.</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>