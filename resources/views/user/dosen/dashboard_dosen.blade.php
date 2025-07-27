@extends('layouts.app_kuwu')
@section('title', 'Dashboard Dosen')
@section('content')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<div class="container px-4 py-8 mx-auto">
    <div class="grid grid-cols-1 gap-4 mb-8 md:grid-cols-2 lg:grid-cols-4">
        <!-- Surat yang diajukan -->
        <a href="{{ route('dosen.riwayat', ['status' => 'diajukan']) }}"
            class="block transition-all transform hover:scale-105">
            <div class="p-6 shadow-lg bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-xl">
                <div class="flex flex-col space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-start">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <div class="p-3 mb-3 bg-yellow-300 rounded-full bg-opacity-30">
                                <i class="text-3xl text-white fas fa-file-alt"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-white">Dokumen Diajukan Ormawa</h2>
                        </div>
                        <span class="text-5xl font-bold text-white">{{ $countDiajukan }}</span>
                    </div>
                    <div class="absolute right-2 bottom-2 opacity-10">
                        <i class="text-6xl text-white fas fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </a>
        <!-- Surat sudah tertanda -->
        <a href="{{ route('dosen.riwayat', ['status' => 'disahkan']) }}"
            class="block transition-all transform hover:scale-105">
            <div class="shadow-lg p-9 bg-gradient-to-br from-green-400 to-green-500 rounded-xl">
                <div class="flex flex-col space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-start">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="p-3 mb-3 bg-green-300 rounded-full bg-opacity-30">
                                <i class="text-3xl text-white fas fa-file-signature"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-white">Dokumen Tertanda</h2>
                        </div>
                        <span class="text-5xl font-bold text-white">{{ $countDisahkan }}</span>
                    </div>
                    <div class="absolute right-2 bottom-2 opacity-10">
                        <i class="text-6xl text-white fas fa-check-double"></i>
                    </div>
                </div>
            </div>
        </a>

        <!-- Surat perlu direvisi -->
        <a href="{{ route('dosen.riwayat', ['status' => 'butuh_revisi']) }}"
            class="block transition-all transform hover:scale-105">
            <div class="shadow-lg p-9 bg-gradient-to-br from-red-400 to-red-500 rounded-xl">
                <div class="flex flex-col space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-col items-start">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="p-3 mb-3 bg-red-300 rounded-full bg-opacity-30">
                                <i class="text-3xl text-white fas fa-file-medical-alt"></i>
                            </div>
                            <h2 class="text-lg font-semibold text-white">Sudah Disetujui</h2>
                        </div>
                        <span class="text-5xl font-bold text-white">{{ $countDisetujui }}</span>
                    </div>
                    <div class="absolute right-2 bottom-2 opacity-10">
                        <i class="text-6xl text-white fas fa-exclamation-circle"></i>
                    </div>
                </div>
            </div>
        </a>

    </div>

    <!-- Search and Filter Section -->
    <div class="flex flex-col items-center justify-between mb-8 space-y-4 md:flex-row md:space-y-0">
        <div class="w-full md:w-64">
            <form method="GET" action="{{ route('dosen.dashboard') }}" class="flex">
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Surat"
                        class="w-full py-2 pl-10 pr-4 border rounded-l-lg">
                    <i class="absolute text-gray-400 top-3 left-3 fas fa-search"></i>
                </div>
                <button type="submit" class="px-4 py-2 text-white bg-blue-500 rounded-r-lg hover:bg-blue-600">
                    Cari
                </button>
            </form>
        </div>
        <div>
            <form method="GET" action="{{ route('dosen.dashboard') }}">
                <select name="status" class="px-4 py-2 border rounded-lg" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="diajukan" {{ request('status')=='diajukan' ? 'selected' : '' }}>Diajukan</option>
                    <option value="disahkan" {{ request('status')=='disahkan' ? 'selected' : '' }}>Tertanda</option>
                    <option value="butuh revisi" {{ request('status')=='butuh revisi' ? 'selected' : '' }}>Perlu
                        direvisi</option>
                    <option value="sudah direvisi" {{ request('status')=='sudah direvisi' ? 'selected' : '' }}>Sudah
                        direvisi</option>
                </select>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="w-full p-4 overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">No. Surat</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Tanggal Pengajuan</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nama Pengaju</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Nama Pemohon</th>

                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-xs font-medium text-left text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @if ($dokumens->isEmpty())
                <tr>
                    <td colspan="6" class="py-8 text-center">
                        <div class="flex flex-col items-center justify-center">
                            <i class="text-4xl text-gray-400 fas fa-inbox"></i>
                            <p class="mt-2 text-gray-600">Tidak ada data yang tersedia.</p>
                        </div>
                    </td>
                </tr>
                @endif
                @foreach($dokumens as $dokumen)
                <tr data-id="{{ $dokumen->id }}">
                    <td class="px-6 py-4 whitespace-nowrap" data-nomor>{{ $dokumen->nomor_surat }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-tanggal>{{ $dokumen->tanggal_pengajuan }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-namaPengirim>{{ $dokumen->ormawa->namaMahasiswa }}</td>
                    <td class="px-6 py-4 whitespace-nowrap" data-perihal>{{ $dokumen->nama_pemohon }}</td>

                    <td class="px-6 py-4 whitespace-nowrap" data-status>
                        @php
                        $statusClass = match($dokumen->status_dokumen) {
                        'diajukan' => 'bg-yellow-100 text-yellow-800',
                        'disahkan' => 'bg-green-100 text-green-800',
                        'butuh revisi' => 'bg-red-100 text-red-800',
                        'sudah direvisi' => 'bg-blue-100 text-blue-800',
                        default => 'bg-gray-100 text-gray-800'
                        };
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                            {{ ucfirst($dokumen->status_dokumen) }}
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
</div>

<!-- Modal -->
<div id="documentModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"></div>

        <div class="relative w-full max-w-lg bg-white rounded-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Detail Dokumen</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-500">
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

            <!-- QR Code Editor Modal Content -->
            <div id="qrCodeEditor" class="hidden">
                <div class="relative w-full h-[600px] bg-gray-100">
                    <!-- PDF Preview -->
                    <iframe id="pdfFrame" class="w-full h-full"></iframe>

                    <!-- Draggable & Resizable QR Code Container -->
                    <div id="qrCode" class="absolute hidden bg-white rounded-lg shadow-lg cursor-move"
                        style="width: 100px; height: 100px; top: 50px; left: 50px;">
                        <img id="qrImage" src="" alt="QR Code" class="object-contain w-full h-full" />
                        <!-- Resize handle -->
                        <div
                            class="absolute bottom-0 right-0 w-4 h-4 bg-blue-500 rounded-full opacity-50 cursor-se-resize">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-4 space-x-2">
                    <button onclick="saveQrPosition()"
                        class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                        Simpan Posisi
                    </button>
                    <button onclick="cancelQrPosition()"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                        Batal
                    </button>
                </div>
            </div>

            <div class="flex flex-col justify-end px-6 py-4 space-y-2 border-t border-gray-200 md:flex-row md:space-y-0 md:space-x-3"
                id="modalButtons">
                <!-- Download button - visible only when status is 'disahkan' -->
                <button onclick="downloadDocument()"
                    class="hidden px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Download
                </button>

                <!-- View button - always visible -->
                <button onclick="viewDocument()"
                    class="px-4 py-2 text-white bg-yellow-500 rounded-md hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                    Lihat
                </button>

                <!-- Approve button - visible only when status is 'diajukan' -->
                <button onclick="approveDokumen()"
                    class="hidden px-4 py-2 text-white bg-green-500 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                    Setujui
                </button>

                <!-- Close button - always visible -->
                <button onclick="closeModal()"
                    class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    Tutup
                </button>

                <!-- QR Code button - visible only when status is 'disetujui' -->
                <button onclick="editQrCode()"
                    class="hidden px-4 py-2 text-white bg-green-600 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    Bubuhkan QR Code
                </button>
            </div>


        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
<script>
    let currentDocumentId = null;
    let currentFileUrl = null;

    function showModal(documentId, pdfUrl) {
        currentDocumentId = documentId;
        currentFileUrl = pdfUrl;

        // Fetch document details
        fetch(`/dosen/dokumen/${documentId}`)
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
                            <p class="text-sm font-medium text-gray-500">Perihal</p>
                            <p class="mt-1">${data.perihal}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1">${data.status_dokumen}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Keterangan</p>
                            <p class="mt-1">${data.keterangan || '-'}</p>
                        </div>
                        ${data.keterangan_revisi ? `
                        <div>
                            <p class="text-sm font-medium text-gray-500">Keterangan Revisi</p>
                            <p class="mt-1 text-red-600">${data.keterangan_revisi}</p>
                        </div>
                        ` : ''}
                        ${data.keterangan_pengirim && data.status_dokumen.toLowerCase() === 'sudah direvisi' ? `
                        <div>
                            <p class="text-sm font-medium text-gray-500">Keterangan Dari Ormawa</p>
                            <p class="mt-1 text-blue-600">${data.keterangan_pengirim}</p>
                        </div>
                        ` : ''}
                    </div>
                `;

                // Get all action buttons
                const downloadButton = document.querySelector('button[onclick="downloadDocument()"]');
                const approveButton = document.querySelector('button[onclick="approveDokumen()"]');
                const qrCodeButton = document.querySelector('button[onclick="editQrCode()"]');
                const viewButton = document.querySelector('button[onclick="viewDocument()"]');

                // First, hide all action buttons except view and close
                downloadButton.classList.add('hidden');
                approveButton.classList.add('hidden');
                qrCodeButton.classList.add('hidden');

                // View button is always visible
                viewButton.classList.remove('hidden');

                // Show specific buttons based on status
                switch(data.status_dokumen.toLowerCase()) {
                    case 'diajukan':
                        approveButton.classList.remove('hidden');
                        break;

                    case 'disetujui':
                        qrCodeButton.classList.remove('hidden');
                        qrCodeButton.removeAttribute('disabled');
                        qrCodeButton.classList.remove('opacity-50', 'cursor-not-allowed');
                        qrCodeButton.classList.add('hover:bg-green-700');
                        break;

                    case 'disahkan':
                        downloadButton.classList.remove('hidden');
                        break;

                    default:
                        // For any other status, just show view button
                        break;
                }

                document.getElementById('documentModal').classList.remove('hidden');
            });
    }

    function closeModal() {
        document.getElementById('documentModal').classList.add('hidden');
        document.getElementById('qrCodeEditor').classList.add('hidden');
        document.getElementById('modalContent').classList.remove('hidden');
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

    function generateQrCode(documentId) {
        if (!documentId) return;

        const button = document.querySelector(`button[onclick="generateQrCode(${documentId})"]`);
        const originalText = button.innerHTML;
        button.innerHTML = 'Generating...';
        button.disabled = true;

        fetch(`/dosen/dokumen/${documentId}/generate-qr`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('modalContent').classList.add('hidden');
                document.getElementById('qrCodeEditor').classList.remove('hidden');

                document.getElementById('pdfFrame').src = currentFileUrl;

                const qrCode = document.getElementById('qrCode');
                const qrImage = document.getElementById('qrImage');

                qrImage.onload = function() {
                    qrCode.classList.remove('hidden');
                    initializeInteract();
                };

                qrImage.src = data.qrCodeUrl;

                qrCode.style.transform = 'translate(50px, 50px)';
                qrCode.setAttribute('data-x', 50);
                qrCode.setAttribute('data-y', 50);
                qrCode.style.width = '100px';
                qrCode.style.height = '100px';
            } else {
                alert(data.message || 'Failed to generate QR Code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating QR Code');
        })
        .finally(() => {
            button.innerHTML = originalText;
            button.disabled = false;
        });
    }

    function initializeInteract() {
        interact('#qrCode')
            .draggable({
                inertia: true,
                modifiers: [
                    interact.modifiers.restrictRect({
                        restriction: 'parent',
                        endOnly: true
                    })
                ],
                autoScroll: true,
                listeners: {
                    move: dragMoveListener
                }
            })
            .resizable({
                edges: { left: true, right: true, bottom: true, top: true },
                restrictEdges: {
                    outer: 'parent',
                    endOnly: true,
                },
                restrictSize: {
                    min: { width: 30, height: 30 },
                    max: { width: 200, height: 200 },
                },
                inertia: true,
                listeners: {
                    move: resizeMoveListener
                }
            });
    }

    function dragMoveListener(event) {
        const target = event.target;
        const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
        const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    function resizeMoveListener(event) {
        const target = event.target;
        let x = (parseFloat(target.getAttribute('data-x')) || 0);
        let y = (parseFloat(target.getAttribute('data-y')) || 0);

        // Update element width/height
        target.style.width = `${event.rect.width}px`;
        target.style.height = `${event.rect.height}px`;

        // Translate when resizing from top or left edges
        x += event.deltaRect.left;
        y += event.deltaRect.top;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    function saveQrPosition() {
        const qrElement = document.getElementById('qrCode');
        const rect = qrElement.getBoundingClientRect();
        const containerRect = document.getElementById('pdfFrame').getBoundingClientRect();

        // Calculate relative position
        const position = {
            x: (parseFloat(qrElement.getAttribute('data-x')) || 0),
            y: (parseFloat(qrElement.getAttribute('data-y')) || 0),
            width: parseFloat(qrElement.style.width),
            height: parseFloat(qrElement.style.height)
        };

        fetch(`/dosen/dokumen/${currentDocumentId}/save-qr-position`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(position)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Posisi QR code berhasil disimpan');
                location.reload();
            } else {
                alert(data.message || 'Gagal menyimpan posisi QR code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Gagal menyimpan posisi QR code');
        });
    }

    function cancelQrPosition() {
        document.getElementById('qrCodeEditor').classList.add('hidden');
        document.getElementById('modalContent').classList.remove('hidden');
    }

    function editQrCode() {
        if (!currentDocumentId) return;

        // Generate QR Code first
        fetch(`/dosen/dokumen/${currentDocumentId}/generate-qr`, {
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
                window.location.href = `/dosen/dokumen/${currentDocumentId}/edit-qr`;
            } else {
                alert(data.message || 'Gagal membuat QR Code');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error generating QR Code');
        });
    }

    function approveDokumen() {
        if (!currentDocumentId) return;

        Swal.fire({
            title: 'Konfirmasi Persetujuan',
            text: "Apakah Anda yakin ingin menyetujui dokumen ini?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Setujui!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(`/dosen/dokumen/${currentDocumentId}/approve`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            'Berhasil!',
                            'Dokumen telah disetujui.',
                            'success'
                        ).then(() => {
                            location.reload(); // Reload the page to update the status
                        });
                    } else {
                        Swal.fire(
                            'Gagal!',
                            data.message || 'Terjadi kesalahan saat menyetujui dokumen.',
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire(
                        'Error!',
                        'Terjadi kesalahan saat menyetujui dokumen.',
                        'error'
                    );
                });
            }
        });
    }



    // Close modal when clicking outside
    document.getElementById('documentModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endsection
