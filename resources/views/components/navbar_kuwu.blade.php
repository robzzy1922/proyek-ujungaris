<header class="bg-white px-6 py-4 shadow flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
    </div>
    <div class="flex items-center space-x-4">
        <button class="relative">
            <i class="ti ti-bell text-xl text-gray-500"></i>
        </button>
        @if(Auth::guard('kuwu')->user()->photo)
            <img src="{{ asset('storage/' . Auth::guard('kuwu')->user()->photo) }}"
                 alt="Profile Photo"
                 class="w-10 h-10 rounded-full object-cover">
        @else
            <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center">
                <i class="ti ti-user text-gray-600"></i>
            </div>
        @endif
        <div class="text-right">
            <p class="text-sm font-semibold text-gray-800">{{ Auth::guard('kuwu')->user()->nama_kuwu }}</p>
            <p class="text-xs text-gray-500">Kuwu</p>
        </div>
        <i class="ti ti-chevron-down text-gray-500"></i>
    </div>
</header>
