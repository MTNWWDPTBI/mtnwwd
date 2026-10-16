<x-app-layout>
    <!-- Header / Banner Welcome -->
    <div class="bg-blue-900 py-12 px-4 sm:px-6 lg:px-8 text-white">
        <div class="max-w-7xl mx-auto">
            <h1 class="text-3xl font-bold">Selamat datang, {{ Auth::user()->name }}</h1>
            <p class="mt-2 text-blue-200">Temukan sparepart yang Anda butuhkan dengan cepat dan mudah.</p>
            
            <!-- Form Pencarian -->
            <div class="mt-6 max-w-3xl flex">
                <form action="#" method="GET" class="w-full flex">
                    <input type="text" name="search" placeholder="Cari nama part, part number, nama mesin, atau kata kunci..." class="w-full px-4 py-3 rounded-l-md text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-600 px-6 py-3 rounded-r-md hover:bg-blue-700 transition">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
            
            <!-- Kategori Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white p-6 rounded-lg shadow-sm text-center border-t-4 border-blue-500">
                    <div class="text-blue-500 mb-2"><svg class="w-8 h-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></div>
                    <h3 class="font-bold text-gray-800">Semua Part</h3>
                    <p class="text-sm text-gray-500">{{ $totalSpareparts }}+ part</p>
                </div>
                <!-- Kamu bisa menduplikasi card di atas untuk kategori Electrical, Pneumatic, dll -->
            </div>

            <!-- Mesin Populer -->
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-4">Mesin Populer</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    @foreach($machines as $machine)
                    <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 hover:shadow-md transition cursor-pointer text-center">
                        <div class="bg-gray-100 h-24 rounded w-full flex items-center justify-center mb-3">
                            <span class="text-gray-400 text-xs">Foto Mesin</span>
                        </div>
                        <h4 class="font-semibold text-gray-700 text-sm">{{ $machine->machine_name }}</h4>
                        <p class="text-xs text-gray-500">{{ $machine->machine_code }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>