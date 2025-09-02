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
        min-height: 600px;
        overflow: hidden;
        position: relative;
    }

    #pdfViewer {
        background-color: white;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        max-width: 90%;
        width: auto;
        height: auto;
        max-height: calc(100vh - 200px);
        position: relative;
    }

    #qrCode {
        position: absolute;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.9);
        border: 2px dashed rgba(59, 130, 246, 0.5);
        cursor: default;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 4px;
    }

    #qrCode:hover {
        border-color: rgba(59, 130, 246, 0.8);
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
        background: rgba(59, 130, 246, 0.8);
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
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        z-index: 1001;
        transition: all 0.2s;
    }

    .move-handle:hover {
        background: rgba(37, 99, 235, 1);
        transform: translate(-50%, -50%) scale(1.1);
    }

    .page-controls {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
        align-items: center;
        background: white;
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .page-controls button {
        padding: 0.5rem 1rem;
        background: #4B5563;
        color: white;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .page-controls button:hover {
        background: #374151;
    }

    .page-controls button:disabled {
        background: #9CA3AF;
        cursor: not-allowed;
    }

    .resize-handle {
        position: absolute;
        right: -5px;
        bottom: -5px;
        width: 12px;
        height: 12px;
        background: rgba(59, 130, 246, 0.8);
        cursor: se-resize;
        border: 2px solid white;
        border-radius: 50%;
        z-index: 1002;
    }

    .resize-handle:hover {
        background: rgba(37, 99, 235, 1);
        transform: scale(1.2);
    }

    .loading-spinner {
        display: inline-block;
        width: 20px;
        height: 20px;
        border: 3px solid #f3f3f3;
        border-top: 3px solid #3498db;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .qr-error {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #666;
        font-size: 12px;
        text-align: center;
        background: #f8f9fa;
        border-radius: 4px;
    }
</style>

<div class="container mx-auto px-4 py-8 max-w-6xl">
    @if(isset($dokumen))
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

        <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Edit Posisi QR Code</h1>
            <p class="text-gray-600">Dokumen: <span class="font-medium">{{ $dokumen->nama_dokumen ?? $dokumen->nomor_surat ?? 'Tidak diketahui' }}</span></p>
        </div>

        @if(config('app.debug'))
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <h4 class="font-medium text-yellow-800">Debug Info:</h4>
            <p class="text-sm text-yellow-700">QR Path: {{ $dokumen->qr_code_path ?? 'Not set' }}</p>
            <p class="text-sm text-yellow-700">QR URL: {{ $dokumen->qr_code_path ? asset('storage/' . $dokumen->qr_code_path) : 'N/A' }}</p>
            <p class="text-sm text-yellow-700">File exists: {{ $dokumen->qr_code_path && Storage::disk('public')->exists($dokumen->qr_code_path) ? 'Yes' : 'No' }}</p>
        </div>
        @endif

        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 011-1h2a1 1 0 011 1v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Petunjuk Penempatan QR Code:</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pastikan QR code ditempatkan di area yang kosong dan tidak menutupi teks penting</li>
                            <li>Disarankan menempatkan QR code di pojok kanan bawah dokumen</li>
                            <li>Ukuran QR code dapat disesuaikan dengan menarik lingkaran biru di sudut kanan bawah</li>
                            <li>Gunakan ikon panah di tengah QR code untuk memindahkan posisi</li>
                            <li>Pastikan QR code tidak menutupi konten penting pada dokumen</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <div id="pdfContainer" class="relative w-full">
                <div id="loadingMessage" class="text-center text-white py-8">
                    <div class="loading-spinner mx-auto mb-2"></div>
                    <span>Memuat dokumen...</span>
                </div>

                <canvas id="pdfViewer" class="w-full h-full" style="display: none;"></canvas>

                <div class="page-controls" style="display: none;" id="pageControls">
                    <button id="prevPage" disabled>Previous</button>
                    <span id="pageInfo">Page: <span id="pageNum">1</span> / <span id="pageCount">1</span></span>
                    <button id="nextPage">Next</button>
                </div>

                {{-- Debug info (sementara, bisa dihapus nanti) --}}
                <p>Path di DB: {{ $dokumen->qr_code_path }}</p>
                <p>URL penuh: {{ Storage::url($dokumen->qr_code_path) }}</p>
                <p>File exists: {{ Storage::disk('public')->exists($dokumen->qr_code_path) ? 'Yes' : 'No' }}</p>

                <div id="qrCode" class="absolute bg-white rounded-lg shadow-lg"
                     style="width: 100px; height: 100px; top: 50px; left: 50px; display: none;">
                    @if($dokumen->qr_code_path && Storage::disk('public')->exists($dokumen->qr_code_path))
                        <img id="qrImage"
                             src="{{ Storage::url($dokumen->qr_code_path) }}"
                             alt="QR Code"
                             class="object-contain w-full h-full"
                             onerror="handleQrImageError(this)"/>
                    @else
                        <div class="qr-error">
                            <div>
                                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                QR Code<br>Not Found
                            </div>
                        </div>
                    @endif
                    <div id="moveHandle" class="move-handle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M7.646.146a.5.5 0 0 1 .708 0l2 2a.5.5 0 0 1-.708.708L8.5 1.707V5.5a.5.5 0 0 1-1 0V1.707L6.354 2.854a.5.5 0 1 1-.708-.708l2-2zM8 10a.5.5 0 0 1 .5.5v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 14.293V10.5A.5.5 0 0 1 8 10zM.146 8.354a.5.5 0 0 1 0-.708l2-2a.5.5 0 1 1 .708.708L1.707 7.5H5.5a.5.5 0 0 1 0 1H1.707l1.147 1.146a.5.5 0 0 1-.708.708l-2-2zM10 8a.5.5 0 0 1 .5-.5h3.793l-1.147-1.146a.5.5 0 0 1 .708-.708l2 2a.5.5 0 0 1 0 .708l-2 2a.5.5 0 0 1-.708-.708L14.293 8.5H10.5A.5.5 0 0 1 10 8z"/>
                        </svg>
                    </div>
                    <div class="resize-handle"></div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('admin.dashboard') }}"
               class="px-6 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Batal
            </a>
            <button onclick="saveQrPosition({{ $dokumen->id }})" id="saveButton"
                    class="px-6 py-2 text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors">
                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                Simpan Posisi
            </button>
        </div>
    @else
        <div class="text-center py-12">
            <div class="bg-red-50 border border-red-200 rounded-lg p-8 max-w-md mx-auto">
                <svg class="w-12 h-12 text-red-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Dokumen Tidak Ditemukan</h3>
                <p class="text-red-600 mb-4">Dokumen yang Anda cari tidak dapat ditemukan atau telah dihapus.</p>
                <a href="{{ route('admin.dashboard') }}"
                   class="inline-flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    @endif
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/interact.js/1.10.11/interact.min.js"></script>
<script>
    let pdfDoc = null;
    let pageNum = 1;
    let pageRendering = false;
    let pageNumPending = null;
    let pdfLoaded = false;
    let canvasScale = 1;

    // Konfigurasi PDF.js
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.worker.min.js';

    async function renderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
            return;
        }

        pageRendering = true;

        try {
            const page = await pdfDoc.getPage(num);
            const canvas = document.getElementById('pdfViewer');
            const context = canvas.getContext('2d');

            // Calculate scale based on container width
            const containerWidth = canvas.parentElement.clientWidth;
            const viewport = page.getViewport({ scale: 1 });

            // Increase scale for better visibility
            canvasScale = Math.min(
                (containerWidth - 40) / viewport.width,
                (window.innerHeight - 250) / viewport.height
            ) * 1.2;

            const scaledViewport = page.getViewport({ scale: canvasScale });

            canvas.width = scaledViewport.width;
            canvas.height = scaledViewport.height;

            const renderContext = {
                canvasContext: context,
                viewport: scaledViewport
            };

            await page.render(renderContext).promise;

            // Hide loading and show canvas
            document.getElementById('loadingMessage').style.display = 'none';
            canvas.style.display = 'block';
            document.getElementById('pageControls').style.display = 'flex';

            // Show QR code after PDF is loaded
            const qrElement = document.getElementById('qrCode');
            if (qrElement) {
                qrElement.style.display = 'block';

                // Check if we have saved position data
                const savedX = {{ $dokumen->qr_position_x ?? 'null' }};
                const savedY = {{ $dokumen->qr_position_y ?? 'null' }};
                const savedWidth = {{ $dokumen->qr_width ?? 'null' }};
                const savedHeight = {{ $dokumen->qr_height ?? 'null' }};

                if (savedX !== null && savedY !== null) {
                    // Use saved position (convert from percentage to pixels)
                    const posX = (savedX / 100) * scaledViewport.width;
                    const posY = (savedY / 100) * scaledViewport.height;
                    qrElement.style.left = posX + 'px';
                    qrElement.style.top = posY + 'px';

                    // Use saved size if available
                    if (savedWidth !== null && savedHeight !== null) {
                        const width = (savedWidth / 100) * scaledViewport.width;
                        const height = (savedHeight / 100) * scaledViewport.height;
                        qrElement.style.width = width + 'px';
                        qrElement.style.height = height + 'px';
                    }

                    console.log('Using saved QR position:', { posX, posY, width: savedWidth ? width : 'default', height: savedHeight ? height : 'default' });
                } else {
                    // Position QR code at bottom right by default
                    const defaultX = scaledViewport.width - 120;
                    const defaultY = scaledViewport.height - 120;
                    qrElement.style.left = Math.max(20, defaultX) + 'px';
                    qrElement.style.top = Math.max(20, defaultY) + 'px';
                    console.log('Using default QR position at bottom right');
                }

                // Reset any previous transforms
                qrElement.setAttribute('data-x', 0);
                qrElement.setAttribute('data-y', 0);
                qrElement.style.transform = 'translate(0px, 0px)';
            }

            pageRendering = false;
            pdfLoaded = true;

            if (pageNumPending !== null) {
                renderPage(pageNumPending);
                pageNumPending = null;
            }

            // Update page controls
            document.getElementById('pageNum').textContent = num;
            document.getElementById('prevPage').disabled = num <= 1;
            document.getElementById('nextPage').disabled = num >= pdfDoc.numPages;

            console.log('PDF page rendered successfully');
        } catch (error) {
            console.error('Error rendering page:', error);
            document.getElementById('loadingMessage').innerHTML = `<div class="text-red-400">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span>Error memuat dokumen: ${error.message}</span>
                </div>`;
            pageRendering = false;
        }
    }

    function queueRenderPage(num) {
        if (pageRendering) {
            pageNumPending = num;
        } else {
            renderPage(num);
        }
    }

    function onPrevPage() {
        if (pageNum <= 1) return;
        pageNum--;
        queueRenderPage(pageNum);
    }

    function onNextPage() {
        if (pageNum >= pdfDoc.numPages) return;
        pageNum++;
        queueRenderPage(pageNum);
    }

    // Inisialisasi PDF
    async function initPDF() {
        try {
            const url = "{{ route('admin.dokumen.view', $dokumen->id) }}";
            console.log('Loading PDF from:', url);

            const loadingMessage = document.getElementById('loadingMessage');

            const loadingTask = pdfjsLib.getDocument(url);

            loadingTask.onProgress = function(progress) {
                if (progress.total) {
                    const percent = Math.round((progress.loaded / progress.total) * 100);
                    loadingMessage.innerHTML = `<div class="loading-spinner mx-auto mb-2"></div>
                        <span>Memuat dokumen... ${percent}%</span>`;
                }
            };

            pdfDoc = await loadingTask.promise;
            console.log('PDF loaded successfully, pages:', pdfDoc.numPages);

            document.getElementById('pageCount').textContent = pdfDoc.numPages;

            // Render halaman pertama
            await renderPage(pageNum);

            // Setup event listeners
            document.getElementById('prevPage').addEventListener('click', onPrevPage);
            document.getElementById('nextPage').addEventListener('click', onNextPage);
        } catch (error) {
            console.error('Error loading PDF:', error);
            document.getElementById('loadingMessage').innerHTML = `<div class="text-red-400">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                    </svg>
                    <span>Gagal memuat dokumen PDF. Pastikan file PDF valid dan dapat diakses.</span>
                </div>`;
            // Still show QR code if PDF fails to load
            const qrElement = document.getElementById('qrCode');
            if (qrElement) {
                qrElement.style.display = 'block';
                // Position QR code in the center of the container
                const container = document.getElementById('pdfContainer');
                if (container) {
                    const containerWidth = container.clientWidth;
                    const containerHeight = container.clientHeight;
                    qrElement.style.left = ((containerWidth / 2) - 50) + 'px';
                    qrElement.style.top = ((containerHeight / 2) - 50) + 'px';
                }
            }

            // Enable save button even if PDF fails to load
            const saveButton = document.getElementById('saveButton');
            if (saveButton) {
                saveButton.disabled = false;
            }
        }
    }

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
                    min: { width: 40, height: 40 },
                    max: { width: 200, height: 200 },
                },
                inertia: true,
                listeners: {
                    move: resizeMoveListener
                }
            });

        console.log('Interact.js initialized successfully');
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

    // Fungsi untuk menghitung posisi relatif
    function calculateRelativePosition(element, container) {
        const elementRect = element.getBoundingClientRect();
        const containerRect = container.getBoundingClientRect();

        // Get transform values
        const transform = element.style.transform;
        let translateX = 0, translateY = 0;

        if (transform && transform.includes('translate')) {
            const matches = transform.match(/translate\(([^,]+),\s*([^)]+)\)/);
            if (matches) {
                translateX = parseFloat(matches[1]) || 0;
                translateY = parseFloat(matches[2]) || 0;
            }
        }

        // Calculate actual position including transforms
        const actualLeft = elementRect.left - containerRect.left;
        const actualTop = elementRect.top - containerRect.top;

        // Convert to percentage
        const x = (actualLeft / containerRect.width) * 100;
        const y = (actualTop / containerRect.height) * 100;
        const width = (elementRect.width / containerRect.width) * 100;
        const height = (elementRect.height / containerRect.height) * 100;

        console.log('Position calculation:', {
            elementRect,
            containerRect,
            actualLeft,
            actualTop,
            x, y, width, height,
            page: pageNum
        });

        return {
            x: Math.max(0, Math.min(95, x)),
            y: Math.max(0, Math.min(95, y)),
            width: Math.max(1, Math.min(50, width)),
            height: Math.max(1, Math.min(50, height)),
            page: pageNum
        };
    }

    function saveQrPosition(dokumenId) {
        const qrElement = document.getElementById('qrCode');
        const container = document.getElementById('pdfViewer');

        if (!qrElement || !container) {
            Swal.fire({
                title: 'Error!',
                text: 'Elemen QR code atau PDF viewer tidak ditemukan',
                icon: 'error'
            });
            return;
        }

        // Check if QR image loaded properly
        const qrImage = document.getElementById('qrImage');
        const qrError = qrElement.querySelector('.qr-error');

        // If QR code doesn't exist yet, we can still save position for it to be generated
        if (!qrImage && qrError) {
            console.log('QR code not found but proceeding with position save');
        }

        const position = calculateRelativePosition(qrElement, container);

        // Validasi posisi
        if (position.x < 0 || position.y < 0 || position.x > 95 || position.y > 95) {
            Swal.fire({
                title: 'Peringatan!',
                text: 'QR Code harus berada di dalam area dokumen',
                icon: 'warning'
            });
            return;
        }

        // Log position data for debugging
        console.log('Saving position:', position);

        // Disable button and show loading
        const saveButton = document.getElementById('saveButton');
        const originalContent = saveButton.innerHTML;
        saveButton.disabled = true;
        saveButton.innerHTML = `<div class="loading-spinner inline-block mr-2"></div>
            Menyimpan...`;

        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            Swal.fire({
                title: 'Error!',
                text: 'CSRF token tidak ditemukan. Silakan refresh halaman.',
                icon: 'error'
            });
            saveButton.disabled = false;
            saveButton.innerHTML = originalContent;
            return;
        }

        fetch(`{{ route('admin.dokumen.saveQrPosition', ['dokumen' => '__DOKUMEN_ID__']) }}`.replace('__DOKUMEN_ID__', dokumenId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify(position)
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                return response.json().then(err => {
                    throw new Error(err.message || `HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: data.message || 'QR Code berhasil ditempel dan dokumen sudah disahkan.',
                    icon: 'success',
                    confirmButtonText: 'OK',
                    timer: 2500,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = "{{ route('admin.dashboard') }}";
                });
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: data.message || 'Posisi QR Code gagal disimpan.',
                    icon: 'error'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Terjadi kesalahan saat menyimpan posisi QR code',
                icon: 'error'
            });
        })
        .finally(() => {
            // Re-enable button
            saveButton.disabled = false;
            saveButton.innerHTML = originalContent;
        });
    }

    function handleQrImageError(img) {
        console.error('Failed to load QR code image:', img.src);
        img.style.display = 'none';
        const errorDiv = document.createElement('div');
        errorDiv.className = 'qr-error';
        errorDiv.innerHTML = `<div>
                <svg class="w-8 h-8 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                QR Code<br>Load Error
            </div>`;
        img.parentElement.appendChild(errorDiv);
    }

    // Inisialisasi saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Initializing PDF and Interact...');

        // Check if QR code image exists and is loaded
        const qrImage = document.getElementById('qrImage');
        if (qrImage) {
            qrImage.onload = function() {
                console.log('QR image loaded successfully');
            };
            qrImage.onerror = function() {
                console.error('QR image failed to load');
                handleQrImageError(this);
            };
        }

        initPDF();
        initializeInteract();

        // Handle window resize
        let resizeTimeout;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                if (pdfDoc && pdfLoaded) {
                    renderPage(pageNum);
                }
            }, 300);
        });
    });
</script>
@endsection
