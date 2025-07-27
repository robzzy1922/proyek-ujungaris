<aside class="w-64 bg-white shadow-md flex flex-col justify-between">
    <div>
        <div class="flex items-center px-6 py-4 border-b">
            <div class="w-12 h-12 rounded-full bg-black mr-3"></div>
            <div>
                <h1 class="text-lg font-bold leading-tight">ADMIN PANEL</h1>
                <p class="text-sm text-gray-500">Desa Ujungaris</p>
            </div>
        </div>
        <nav class="mt-6 space-y-2 px-4">
            <!-- Dashboard -->
            <a href="{{ route('ormawa.dashboard') }}" class="flex items-center px-3 py-2 bg-blue-100 text-blue-600 rounded-lg font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7m-7-7v18"/>
                </svg>
                Dashboard
            </a>

            <!-- Kelola Dokumen -->
            <p class="text-sm text-gray-500 mt-4 ml-2">Kelola Dokumen</p>
            <a href="{{ route('ormawa.pengajuan') }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16v-8m4 4h-8"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4h16v16H4V4z"/>
                </svg>
                Ajukan Dokumen
            </a>
            <a href="{{ route('ormawa.riwayat') }}" class="flex items-center px-3 py-2 text-gray-700 hover:bg-gray-100 rounded-lg">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat Dokumen
            </a>
        </nav>
    </div>

    <!-- Logout -->
    <div class="px-4 py-4">
        <form method="POST" action="{{ route('ormawa.logout') }}">
            @csrf
            <button type="submit" class="flex items-center text-gray-600 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</aside>
