@extends('layouts.app_admin')
@section('title', 'Riwayat Pengajuan')
@section('content')
<div class="container flex-grow px-4 mx-auto mt-8 max-w-5xl">
    <h1 class="mb-6 text-xl md:text-2xl font-bold">Riwayat Pengajuan</h1>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-4">
        <div class="w-full md:w-64">
            <form method="GET" action="{{ route('admin.riwayat') }}" class="flex">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Pengajuan"
                        class="py-2 pr-4 pl-10 w-full rounded-l-lg border text-sm">
                    <svg class="absolute top-2.5 left-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button type="submit" class="px-3 py-2 text-white bg-blue-500 rounded-r-lg hover:bg-blue-600 text-sm">
                    Cari
                </button>
            </form>
        </div>
        <div class="w-full md:w-auto">
            <form method="GET" action="{{ route('admin.riwayat') }}">
                <select name="status" class="w-full md:w-auto px-4 py-2 rounded-lg border text-sm"
                    onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ request('status')=='diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="disahkan" {{ request('status')=='disahkan' ? 'selected' : '' }}>Disahkan</option>
                    <option value="disetujui" {{ request('status')=='disetujui' ? 'selected' : '' }}>Disetujui</option>
                </select>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto bg-white rounded-lg shadow-md">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        No. Surat</th>
                    <th
                        class="hidden md:table-cell px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Tanggal</th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Nama Pemohon</th>

                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Status</th>
                    <th
                        class="px-3 md:px-6 py-2 md:py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                        Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if ($dokumens->isEmpty())
                <tr>
                    <td colspan="6" class="py-8 text-center">
                        <div class="flex flex-col justify-center items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 md:w-12 md:h-12 text-gray-400"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4V12M12 16H12.01M18.364 5.636L15 9M21 12H15M9 12H3M6.636 5.636L10 9M12 12L12 20M6.636 18.364L10 15M18.364 18.364L15 15">
                                </path>
                            </svg>
                            <p class="mt-2 text-sm md:text-base text-gray-600">Belum ada riwayat pengajuan.</p>
                        </div>
                    </td>
                </tr>
                @endif
                @foreach($dokumens as $dokumen)
                <tr class="text-sm md:text-base">
                    <td class="px-3 md:px-6 py-2 md:py-4 whitespace-nowrap">{{ $dokumen->nomor_surat }}</td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap">{{ $dokumen->tanggal_pengajuan }}</td>
                    <td class="px-3 md:px-6 py-2 md:py-4 whitespace-nowrap">{{ $dokumen->nama_pemohon }}</td>

                    <td class="px-3 md:px-6 py-2 md:py-4 whitespace-nowrap">
                        @php
                            $statusClass = match(strtolower($dokumen->status_dokumen)) {
                                'diajukan' => 'bg-yellow-100 text-yellow-800',
                                'disahkan' => 'bg-green-100 text-green-800',
                                'disetujui' => 'bg-blue-100 text-blue-800',
                                default => 'bg-gray-100 text-gray-800'
                            };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ $dokumen->status_dokumen }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900"
                           onclick="showModal({{ $dokumen->id }}, '{{ asset('storage/' . $dokumen->file) }}')">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="flex justify-center mt-4">
        <nav class="inline-flex relative z-0 -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            <a href="#"
                class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-l-md border border-gray-300 hover:bg-gray-50">
                <span class="sr-only">Previous</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
            </a>
            <a href="#"
                class="hidden md:inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">1</a>
            <a href="#"
                class="hidden md:inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">2</a>
            <a href="#"
                class="hidden md:inline-flex relative items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50">3</a>
            <a href="#"
                class="inline-flex relative items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white rounded-r-md border border-gray-300 hover:bg-gray-50">
                <span class="sr-only">Next</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
        </nav>
    </div>
</div>

<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between pb-4 mb-4 border-b">
                    <h3 class="text-2xl font-semibold text-gray-900">Detail Dokumen</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div id="modalContent" class="space-y-4">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showModal(documentId) {
    const modal = document.getElementById('detailModal');
    const modalContent = document.getElementById('modalContent');

    // Show loading state
    modalContent.innerHTML = `
        <div class="flex items-center justify-center py-8">
            <div class="w-8 h-8 border-b-2 border-blue-500 rounded-full animate-spin"></div>
            <span class="ml-2">Memuat dokumen...</span>
        </div>
    `;

    modal.classList.remove('hidden');

    // Fetch document details
    fetch(`/admin/dokumen/${documentId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(response => {
        if (!response.success) {
            throw new Error(response.message || 'Terjadi kesalahan saat memuat dokumen');
        }

        const data = response.data;

        // Update modal content with document details
        modalContent.innerHTML = `
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div class="space-y-4">
                    <div class="p-4 rounded-lg bg-gray-50">
                        <h4 class="mb-4 text-lg font-semibold">Informasi Dokumen</h4>
                        <dl class="space-y-2">
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-600">Nomor Surat:</dt>
                                <dd>${data.nomor_surat || '-'}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-600">Jenis Surat:</dt>
                                <dd>${data.jenis_surat}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-600">Tanggal Pengajuan:</dt>
                                <dd>${data.tanggal_pengajuan}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-600">Nama Pemohon:</dt>
                                <dd>${data.nama_pemohon}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="font-medium text-gray-600">Status:</dt>
                                <dd>
                                    <span class="px-2 py-1 text-sm rounded-full ${getStatusClass(data.status_dokumen)}">
                                        ${data.status_dokumen}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div class="flex flex-col space-y-2">
                        ${data.status_dokumen.toLowerCase() === 'disahkan' ? `
                            <a href="/admin/dokumen/${documentId}/download"
                               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Download Dokumen
                            </a>
                        ` : ''}
                        <a href="/admin/dokumen/${documentId}/view"
                           target="_blank"
                           class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            Lihat di Tab Baru
                        </a>
                    </div>
                </div>
                <div class="h-[600px] border rounded-lg overflow-hidden">
                    <iframe src="/admin/dokumen/${documentId}/view" class="w-full h-full" frameborder="0"></iframe>
                </div>
            </div>
        `;
    })
    .catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = `
            <div class="py-8 text-center text-red-600">
                ${error.message || 'Terjadi kesalahan saat memuat dokumen'}
            </div>
        `;
    });
}

function closeModal() {
    document.getElementById('detailModal').classList.add('hidden');
}

function getStatusClass(status) {
    const statusClasses = {
        'diajukan': 'bg-yellow-100 text-yellow-800',
        'disahkan': 'bg-green-100 text-green-800',
        'butuh revisi': 'bg-red-100 text-red-800',
        'sudah direvisi': 'bg-blue-100 text-blue-800'
    };
    return statusClasses[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
}
</script>
@endsection
