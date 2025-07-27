@extends('layouts.app_kuwu')
@section('title', 'Riwayat Pengesahan')
@section('content')
<div class="container flex-grow max-w-5xl px-4 mx-auto mt-8">
    <h1 class="mb-6 text-2xl font-bold">Riwayat Dokumen</h1>

    <div class="flex flex-col gap-4 mb-4 md:flex-row md:justify-between md:items-center">
        <div class="w-full md:w-64">
            <form method="GET" action="{{ route('kuwu.riwayat') }}" class="flex">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Pengajuan"
                        class="w-full py-2 pl-10 pr-4 border rounded-lg">
                    <svg class="absolute w-5 h-5 text-gray-400 top-3 left-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <button type="submit" class="px-4 py-2 ml-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600">
                    Cari
                </button>
            </form>
        </div>
        <div class="w-full md:w-auto">
            <form method="GET" action="{{ route('kuwu.riwayat') }}">
                <select name="status" class="w-full px-4 py-2 text-sm border rounded-lg md:w-auto"
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
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">No. Surat
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Tanggal
                        Pengajuan</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Nama Pemohon</th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Status
                    </th>
                    <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($documents as $document)
                <tr data-id="{{ $document->id }}">
                    <td class="px-6 py-4 whitespace-nowrap" data-nomor>{{ $document->nomor_surat }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-tanggal>{{ $document->tanggal_pengajuan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-perihal>{{ $document->nama_pemohon }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-status>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                    {{ $document->status_dokumen == 'disahkan' ? 'bg-green-100 text-green-800' : ($document->status_dokumen == 'diajukan' ? 'bg-yellow-100 text-yellow-800' :
                                    ($document->status_dokumen == 'direvisi' ? 'bg-blue-100 text-blue-800' :
                                    'bg-gray-100 text-gray-800')) }}
                                ">
                            {{ ucfirst($document->status_dokumen) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900"
                            onclick="showModal({{ $document->id }}, '{{ asset('storage/' . $document->file) }}')">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data pengajuan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex justify-center mt-4">
        <!-- Pagination component -->
        <nav class="relative z-0 inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
            <a href="#"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50">
                <span class="sr-only">Previous</span>
                <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd" />
                </svg>
            </a>
            <a href="#"
                class="relative items-center hidden px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 md:inline-flex hover:bg-gray-50">1</a>
            <a href="#"
                class="relative items-center hidden px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 md:inline-flex hover:bg-gray-50">2</a>
            <a href="#"
                class="relative items-center hidden px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 md:inline-flex hover:bg-gray-50">3</a>
            <a href="#"
                class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50">
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

<!-- Modal -->
<div id="documentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Detail Dokumen</h3>
                <button onclick="closeModal()" class="absolute text-gray-400 top-4 right-4 hover:text-gray-500">
                    <span class="sr-only">Close</span>
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4" id="modalContent">
                <!-- Content will be loaded here -->
            </div>

            <div class="flex justify-end px-6 py-4 space-x-3 border-t border-gray-200">
                <button onclick="downloadDocument()"
                    class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Download
                </button>
                <button onclick="viewDocument()"
                    class="px-4 py-2 text-white bg-yellow-500 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    Lihat
                </button>
                <button onclick="closeModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
    let currentDocumentId = null;
let currentFileUrl = null;

function showModal(documentId, pdfUrl) {
    currentDocumentId = documentId;
    currentFileUrl = pdfUrl;

    // Fetch document details
    fetch(`/kuwu/dokumen/${documentId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('modalContent').innerHTML = `
                <div class="space-y-4">
                    <div class="p-4 mb-4 bg-gray-100 border border-blue-500 rounded-lg">
                        <iframe src="${currentFileUrl}" width="100%" height="500px"></iframe>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Nomor Surat</p>
                        <p class="mt-1">${data.nomor_surat}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Tanggal Pengajuan</p>
                        <p class="mt-1">${data.tanggal_pengajuan}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Nama Pemohon</p>
                        <p class="mt-1">${data.nama_pemohon}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Status</p>
                        <p class="mt-1">${data.status_dokumen}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Keterangan</p>
                        <p class="mt-1">${data.keterangan || '-'}</p>
                    </div>
                </div>
            `;
        });

    document.getElementById('documentModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('documentModal').classList.add('hidden');
    currentDocumentId = null;
    currentFileUrl = null;
}

function downloadDocument() {
    if (currentFileUrl) {
        const link = document.createElement('a');
        link.href = currentFileUrl;
        link.download = currentFileUrl.split('/').pop();
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function viewDocument() {
    if (currentFileUrl) {
        window.open(currentFileUrl, '_blank');
    }
}

// Close modal when clicking outside
document.getElementById('documentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
