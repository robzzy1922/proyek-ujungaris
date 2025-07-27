@extends('layouts.app_admin')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endpush
@section('title', 'Edit QR Code Position')
@section('content')

<style>
    #pdfContainer {
        background-color: #525659;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    #pdfViewer {
        background-color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 90%;
        width: auto;
        height: auto;
        max-height: calc(100vh - 200px);
    }

    #qrCode {
        position: absolute;
        z-index: 1000;
        background: transparent;
        border: 1px solid rgba(0, 0, 0, 0.1);
        cursor: default;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    #qrImage {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: transparent;
    }

    .move-handle {
        width: 32px;
        height: 32px;
        background: rgba(75, 85, 99, 0.7);
        border: 2px solid white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: move;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        z-index: 1001;
        transition: background-color 0.2s;
    }

    .move-handle:hover {
        background: rgba(55, 65, 81, 1);
    }

    .resize-handle {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 10px;
        height: 10px;
        background: transparent;
        cursor: se-resize;
        border: 2px solid rgba(59, 130, 246, 0.5);
        border-radius: 50%;
    }

    .resize-handle:hover {
        background: rgba(59, 130, 246, 0.2);
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    @if(isset($dokumen))
        <!-- Breadcrumb -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-gray-700 hover:text-blue-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li class="inline-flex items-center">
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500">Edit QR Code</span>
                </li>
            </ol>
        </nav>

        <!-- Petunjuk Penempatan -->
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-4">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Petunjuk Penempatan QR Code:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside">
                            <li>Pastikan QR code ditempatkan di area yang kosong</li>
                            <li>Disarankan menempatkan QR code di pojok kanan bawah dokumen</li>
                            <li>Ukuran QR code dapat disesuaikan dengan menarik sudut kanan bawah</li>
                            <li>Gunakan ikon di tengah QR code untuk memindahkan posisi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Container PDF dan QR -->
        <div id="pdfContainer" class="relative w-full">
            <canvas id="pdfViewer" class="w-full h-full"></canvas>

            <!-- QR Code Draggable -->
            <div id="qrCode" class="absolute bg-white rounded-lg shadow-lg"
                 style="width: 100px; height: 100px; top: 50px; left: 50px;">
                <img id="qrImage"
                     src="{{ asset('storage/' . $dokumen->qr_code_path) }}"
                     alt="QR Code"
                     class="object-contain w-full h-full"/>
                <div id="moveHandle" class="move-handle">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M7.646.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 1.707V5.5a.5.5 0 0 1-1 0V1.707L6.354 2.854a.5.5 0 1 1-.708-.708l2-2zM8 10a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 14.293V10.5A.5.5 0 0 1 8 10zM.146 8.354a.5.5 0 0 1 0-.708l2-2a.5.5 0 1 1 .708.708L1.707 7.5H5.5a.5.5 0 0 1 0 1H1.707l1.147 1.146a.5.5 0 0 1-.708.708l-2-2zM10 8a.5.5 0 0 1 .5-.5h3.793l-1.147-1.146a.5.5 0 0 1 .708-.708l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L14.293 8.5H10.5A.5.5 0 0 1 10 8z"/>
                    </svg>
                </div>
                <div class="resize-handle"></div>
            </div>
        </div>

        <!-- Tombol aksi -->
        <div class="flex justify-end mt-4 space-x-2">
            <button onclick="saveQrPosition({{ $dokumen->id }})"
                    class="px-4 py-2 text-white bg-blue-500 rounded hover:bg-blue-600">
                Simpan Posisi
            </button>
            <a href="{{ route('admin.dashboard') }}"
               class="px-4 py-2 text-gray-700 bg-gray-200 rounded hover:bg-gray-300">
                Batal
            </a>
        </div>
    @else
        <div class="text-center py-8">
            <p class="text-red-500">Dokumen tidak ditemukan.</p>
            <a href="{{ route('admin.dashboard') }}" class="mt-4 inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Kembali ke Dashboard
            </a>
        </div>
    @endif
</div>

<!-- Script PDF.js dan interaksi -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
<script>
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;

    // Konfigurasi PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

    // Inisialisasi interaksi QR Code
    function initializeInteract() {
        interact('#qrCode')
            .draggable({
                enabled: true,
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
                },
                handle: '#moveHandle'
            })
            .resizable({
                edges: { right: true, bottom: true },
                restrictEdges: {
                    outer: 'parent',
                    endOnly: true,
                },
                restrictSize: {
                    min: { width: 30, height: 30 },
                    max: { width: 150, height: 150 },
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

        target.style.width = `${event.rect.width}px`;
        target.style.height = `${event.rect.height}px`;

        x += event.deltaRect.left;
        y += event.deltaRect.top;

        target.style.transform = `translate(${x}px, ${y}px)`;
        target.setAttribute('data-x', x);
        target.setAttribute('data-y', y);
    }

    function calculateRelativePosition(element, container) {
        const elementRect = element.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        return {
            x: ((elementRect.left - containerRect.left) / containerRect.width) * 100,
            y: ((elementRect.top - containerRect.top) / containerRect.height) * 100,
            width: (elementRect.width / containerRect.width) * 100,
            height: (elementRect.height / containerRect.height) * 100,
            page: pageNum
        };
    }

    function saveQrPosition(dokumenId) {
        const qrElement = document.getElementById('qrCode');
        const container = document.getElementById('pdfViewer');
        const position = calculateRelativePosition(qrElement, container);

        fetch(`/admin/dokumen/${dokumenId}/save-qr-position`, {
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
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'QR Code berhasil ditempatkan dan dokumen telah disahkan',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = '{{ route("admin.dashboard") }}';
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Gagal menyimpan posisi QR code',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Gagal menyimpan posisi QR code',
                icon: 'error'
            });
        });
    }

    // Render PDF page
    async function renderPage(num) {
        pageRendering = true;

        try {
            const page = await pdfDoc.getPage(num);
            const canvas = document.getElementById('pdfViewer');
            const context = canvas.getContext('2d');

            const containerWidth = canvas.parentElement.clientWidth;
            const viewport = page.getViewport({ scale: 1 });
            const scale = Math.min(
                (containerWidth - 20) / viewport.width,
                (window.innerHeight - 200) / viewport.height
            ) * 1.2;

            const scaledViewport = page.getViewport({ scale });

            canvas.width = scaledViewport.width;
            canvas.height = scaledViewport.height;

            const renderContext = {
                canvasContext: context,
                viewport: scaledViewport
            };

            await page.render(renderContext).promise;
            pageRendering = false;

            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }
        } catch (error) {
            console.error('Error rendering page:', error);
            pageRendering = false;
        }
    }

    // Initialize PDF
    async function initPDF() {
        try {
            const url = "{{ asset('storage/' . $dokumen->file) }}";
            pdfDoc = await pdfjsLib.getDocument(url).promise;
            renderPage(pageNum);
        } catch (error) {
            console.error('Error loading PDF:', error);
        }
    }

    // Initialize when document is loaded
    document.addEventListener('DOMContentLoaded', function() {
        initPDF();
        initializeInteract();
    });
</script>
@endsection
