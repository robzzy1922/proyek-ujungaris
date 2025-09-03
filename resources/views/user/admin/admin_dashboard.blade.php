@extends('layouts.app_admin')

@section('title', 'Dashboard Admin')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container flex-grow max-w-5xl px-4 mx-auto mt-8">
    <!-- Alert Success -->
    @if(session('success'))
    <div id="alert-success" class="relative p-4 mb-6 text-green-700 bg-green-100 border border-green-400 rounded-lg">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">Berhasil!</span>
            <span class="ml-2">{{ session('success') }}</span>
        </div>
        <!-- Progress bar -->
        <div class="absolute bottom-0 left-0 w-full h-1 bg-green-200">
            <div id="progress-bar" class="h-1 bg-green-600 transition-all duration-[5000ms] ease-linear w-full"></div>
        </div>
    </div>

    <script>
        // Handle alert animation
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.getElementById('alert-success');
            const progressBar = document.getElementById('progress-bar');

            if (alert) {
                // Start progress bar animation
                setTimeout(() => {
                    progressBar.style.width = '0%';
                }, 100);

                // Hide alert after animation
                setTimeout(() => {
                    alert.style.transform = 'translateY(-100%)';
                    alert.style.opacity = '0';
                    alert.style.transition = 'all 300ms ease-in-out';

                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            }
        });
    </script>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <!-- Dokumen Diajukan -->
        <a href="{{ route('admin.riwayat', ['status' => 'diajukan']) }}" class="block">
            <div
                class="p-6 duration-300 bg-yellow-400 shadow-lg rounded-xl hover:shadow-xl hover:bg-yellow-500 transition-color">
                <div class="flex flex-col">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h2 class="text-xl font-bold text-white">Dokumen Diajukan</h2>
                        </div>
                        <span class="text-4xl font-bold text-white">{{ $countDiajukan }}</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Dokumen Disahkan -->
        <a href="{{ route('admin.riwayat', ['status' => 'disahkan']) }}" class="block">
            <div
                class="p-6 duration-300 bg-green-400 shadow-lg rounded-xl hover:shadow-xl hover:bg-green-500 transition-color">
                <div class="flex flex-col">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h2 class="text-xl font-bold text-white">Dokumen Tertanda</h2>
                        </div>
                        <span class="text-4xl font-bold text-white">{{ $countDisahkan }}</span>
                    </div>
                </div>
            </div>
        </a>

        <!-- Dokumen Disetujui -->
        <a href="{{ route('admin.riwayat', ['status' => 'disetujui']) }}" class="block">
            <div class="p-6 duration-300 bg-blue-400 shadow-lg rounded-xl hover:shadow-xl hover:bg-blue-500 transition-color">
                <div class="flex flex-col">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <h2 class="text-xl font-bold text-white">Dokumen Disetujui</h2>
                        </div>
                        <span class="text-4xl font-bold text-white">{{ $countDisetujui }}</span>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="mt-8">
        <div class="flex flex-col items-center justify-between mb-4 space-y-2 md:flex-row md:space-y-0">
            <div class="relative w-full md:w-64">
                <form method="GET" action="{{ route('admin.dashboard') }}" class="flex">
                    <div class="relative flex-grow">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Surat"
                            class="w-full py-2 pl-10 pr-4 border rounded-l-lg">
                        <svg class="absolute w-5 h-5 text-gray-400 top-3 left-3" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                    <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-r-lg hover:bg-blue-600">
                        Cari
                    </button>
                </form>
            </div>
            <div>
                <form method="GET" action="{{ route('admin.dashboard') }}">
                    <div>
                        <select name="status" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="diajukan" {{ request('status')=='diajukan' ? 'selected' : '' }}>Diajukan
                            </option>
                            <option value="disahkan" {{ request('status')=='disahkan' ? 'selected' : '' }}>Disahkan
                            </option>
                            <option value="disetujui" {{ request('status')=='disetujui' ? 'selected' : '' }}>Disetujui
                            </option>
                        </select>
                    </div>
                </form>
            </div>
        </div>
        <div class="w-full p-4 overflow-x-auto bg-white rounded-lg shadow">
            <!-- Added overflow-x-auto -->
            <div class="min-w-full overflow-x-auto">
                <!-- Added wrapper div -->
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                No. Surat</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Jenis Surat</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Tanggal Pengajuan</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Nama Pemohon</th>

                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Status</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if ($dokumens->isEmpty())
                        <tr>
                            <td colspan="6" class="py-8 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 text-gray-400" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4V12M12 16H12.01M18.364 5.636L15 9M21 12H15M9 12H3M6.636 5.636L10 9M12 12L12 20M6.636 18.364L10 15M18.364 18.364L15 15">
                                        </path>
                                    </svg>
                                    <p class="mt-2 text-gray-600">Anda belum memiliki pengajuan surat.</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                        @foreach($dokumens as $dokumen)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $dokumen->nomor_surat }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $dokumen->jenis_surat }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $dokumen->tanggal_pengajuan }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $dokumen->nama_pemohon }}</td>
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
                                    onclick="showModal({{ $dokumen->id }})">
                                    Lihat Detail
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div
            class="inline-block overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
            <div class="px-4 pt-5 pb-4 bg-white sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between pb-4 mb-4 border-b">
                    <h3 class="text-2xl font-semibold text-gray-900">Detail Dokumen</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
                        <span class="sr-only">Close</span>
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
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
    let currentDocumentId = null;
    let currentFileUrl = null;

    function showModal(documentId) {
        currentDocumentId = documentId;

        // Show loading state
        document.getElementById('modalContent').innerHTML = `
            <div class="flex items-center justify-center py-8">
                <div class="w-8 h-8 border-b-2 border-blue-500 rounded-full animate-spin"></div>
                <span class="ml-2">Memuat dokumen...</span>
            </div>
        `;

        document.getElementById('detailModal').classList.remove('hidden');

        // Fetch document details
        fetch(`/admin/dokumen/${documentId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            // Log the raw response for debugging
            console.log('Response status:', response.status);

            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }

            return response.json();
        })
        .then(response => {
            // Log the parsed response for debugging
            console.log('Response data:', response);

            if (!response.success) {
                throw new Error(response.message || 'Terjadi kesalahan saat memuat dokumen');
            }

            const data = response.data;
            currentFileUrl = data.file_url;

            // Update modal content with document details and preview
            document.getElementById('modalContent').innerHTML = `
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
                                <!-- Tombol Download (hanya muncul jika status disahkan) -->
                                <a href="/admin/dokumen/${currentDocumentId}/download"
                                   class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Download Dokumen
                                </a>
                            ` : ''}
                            <!-- Tombol Lihat di Tab Baru (selalu muncul) -->
                            <a href="/admin/dokumen/${currentDocumentId}/view"
                               target="_blank"
                               class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                Lihat di Tab Baru
                            </a>

                            ${data.status_dokumen.toLowerCase() === 'disetujui' ? `
                                <!-- Tombol Bubuhkan QR Code (hanya muncul jika status disetujui) -->
                                <button onclick="editQrCode()"
                                    class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                    Bubuhkan QR Code
                                </button>
                            ` : ''}
                        </div>
                    </div>
                    <div class="h-[600px] border rounded-lg overflow-hidden">
                        <iframe src="/admin/dokumen/${currentDocumentId}/view" class="w-full h-full" frameborder="0"></iframe>
                    </div>
                </div>
            `;

            // If the document needs revision, handle form submission
        })
        .catch(error => {
            console.error('Error loading document:', error);
            document.getElementById('modalContent').innerHTML = `
                <div class="py-8 text-center text-red-600">
                    ${error.message || 'Terjadi kesalahan saat memuat dokumen'}<br>
                    <span class="block mt-2 text-sm">Detail: ${error.toString()}</span>
                </div>
            `;
        });
    }

    function closeModal() {
        document.getElementById('detailModal').classList.add('hidden');
        currentDocumentId = null;
        currentFileUrl = null;
    }

    function getStatusClass(status) {
        const statusClasses = {
            'diajukan': 'bg-yellow-100 text-yellow-800',
            'disahkan': 'bg-green-100 text-green-800',
            'disetujui': 'bg-blue-100 text-blue-800'
        };
        return statusClasses[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
    }

    // Close modal when clicking outside
    document.getElementById('documentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });


    // Helper function to download PDF
    function downloadPDF(url, fileName) {
        fetch(url)
            .then(response => response.blob())
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
            })
            .catch(error => {
                console.error('Error downloading file:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal mengunduh dokumen',
                    text: 'Terjadi kesalahan saat mengunduh dokumen.',
                    confirmButtonColor: '#dc2626'
                });
            });
    }

    function editQrCode() {
        if (!currentDocumentId) return;

        // Generate QR Code first
        fetch(`/admin/dokumen/${currentDocumentId}/generate-qr`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Redirect to QR editor page
                window.location.href = `/admin/dokumen/${currentDocumentId}/edit-qr`;
            } else {
                alert(data.message || 'Gagal membuat QR Code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating QR Code');
        });
    }
</script>
@endsection
{{--  --}}
