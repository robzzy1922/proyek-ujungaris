
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Optional: Add custom fonts (Google Fonts) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Add PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.11.338/pdf.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 50;
        }

        .modal-content {
            position: relative;
            width: 90%;
            height: 90%;
            margin: 2% auto;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }

        #pdf-viewer {
            width: 100%;
            height: 100%;
            border: none;
        }

        .btn-primary {
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        }

        .info-card {
            transition: all 0.3s ease;
        }

        .info-card:hover {
            transform: scale(1.01);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .copy-button {
            transition: all 0.2s ease;
        }

        .copy-button:hover {
            background-color: #f3f4f6;
            color: #1f2937;
        }

        /* Toast notification for copy */
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #1e3a8a;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(100%);
            transition: all 0.3s ease;
            z-index: 50;
        }

        .toast.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 640px) {
            .info-card {
                margin-bottom: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="py-10 min-h-screen bg-gray-50">
        <div class="container px-4 mx-auto max-w-6xl">
            <div class="overflow-hidden mx-auto bg-white rounded-xl border border-gray-200 shadow-lg">
                @if(isset($verified) && $verified)
                    <div class="p-8">
                        <!-- Header dengan Logo -->
                        <div class="flex items-center mb-6">
                            <div class="flex flex-shrink-0 justify-center items-center mr-4 w-16 h-16 text-2xl font-bold text-white bg-blue-600 rounded-full">
                                S<span class="text-yellow-400">X</span>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold text-gray-900">Verifikasi Dokumen Digital</h1>
                                <p class="text-gray-600">SigniX - Sistem Pengelolaan Dokumen Digital</p>
                            </div>
                        </div>

                        <!-- Status Verifikasi -->
                        <div class="flex items-center p-4 mb-6 bg-green-50 rounded-lg border border-green-200 info-card">
                            <div class="flex-shrink-0 mr-3">
                                <div class="flex justify-center items-center w-10 h-10 bg-green-100 rounded-full">
                                    <svg class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            </div>
                            <div>
                                <p class="font-medium text-green-700">Dokumen Disahkan</p>
                                <p class="text-sm text-green-600">
                                    Diverifikasi pada:
                                    @if($dokumen->tanggal_verifikasi)
                                        {{ \Carbon\Carbon::parse($dokumen->tanggal_verifikasi)->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Layout horizontal dengan 2 kolom -->
                        <div class="flex flex-wrap -mx-2">
                            <!-- Kolom Kiri: Informasi Dokumen -->
                            <div class="px-2 w-full lg:w-7/12">
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Nama Pengaju</div>
                                        <div class="font-medium text-gray-900 truncate">
                                            @if($dokumen->ormawa)
                                                {{ $dokumen->ormawa->namaMahasiswa }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Jenis Pengajuan</div>
                                        <div class="font-medium text-gray-900 truncate">{{ $dokumen->perihal }}</div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Tanggal Pengajuan</div>
                                        <div class="font-medium text-gray-900">
                                            {{ \Carbon\Carbon::parse($dokumen->tanggal_pengajuan)->format('d M Y') }} WIB
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Tanggal Disahkan</div>
                                        <div class="font-medium text-gray-900">
                                            @if($dokumen->tanggal_verifikasi)
                                                {{ \Carbon\Carbon::parse($dokumen->tanggal_verifikasi)->format('d M Y, H:i') }} WIB
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Status</div>
                                        <div class="font-medium text-gray-900">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ ucfirst($dokumen->status_dokumen) }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-1 text-sm font-medium text-gray-500">Disahkan oleh</div>
                                        <div class="font-medium text-gray-900 truncate">
                                            @if($dokumen->dosen)
                                                {{ $dokumen->dosen->nama_dosen }} <span class="text-xs text-gray-500">(Dosen)</span>
                                            @elseif($dokumen->kemahasiswaan)
                                                {{ $dokumen->kemahasiswaan->nama_kemahasiswaan }} <span class="text-xs text-gray-500">(Kemahasiswaan)</span>
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Kode Verifikasi -->
                                <div class="mt-4 space-y-3">
                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-2 text-sm font-medium text-gray-500">Link Detail Pengajuan</div>
                                        <div class="flex items-center space-x-2">
                                            <code class="overflow-x-auto flex-1 px-3 py-2 font-mono text-xs tracking-wider whitespace-nowrap bg-gray-50 rounded-lg border border-gray-200">
                                                {{ url()->current() }}
                                            </code>
                                            <button onclick="copyToClipboard('{{ url()->current() }}')"
                                                    class="flex-shrink-0 p-2 text-gray-500 rounded-lg transition-colors hover:text-gray-700 hover:bg-gray-100 copy-button">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-lg info-card hover:bg-gray-50">
                                        <div class="mb-2 text-sm font-medium text-gray-500">Kode Pengesahan</div>
                                        <div class="flex items-center space-x-2">
                                            <code class="flex-1 px-3 py-2 font-mono text-xs tracking-wider bg-gray-50 rounded-lg border border-gray-200">
                                                {{ $dokumen->kode_pengesahan }}
                                            </code>
                                            <button onclick="copyToClipboard('{{ $dokumen->kode_pengesahan }}')"
                                                    class="flex-shrink-0 p-2 text-gray-500 rounded-lg transition-colors hover:text-gray-700 hover:bg-gray-100 copy-button">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Kolom Kanan: QR Code dan Tombol Dokumen -->
                            <div class="flex flex-col justify-center items-center px-2 mt-6 w-full lg:w-5/12 lg:mt-0">
                                @if($dokumen->qr_code_path)
                                <div class="flex flex-col items-center mb-6">
                                    <p class="mb-2 text-sm font-medium text-gray-600">QR Code Verifikasi</p>
                                    <div class="p-4 w-48 h-48 bg-white rounded-lg shadow-md transition-shadow duration-300 hover:shadow-lg">
                                        <img src="{{ Storage::url($dokumen->qr_code_path) }}"
                                             alt="QR Code Verifikasi"
                                             class="object-contain w-full h-full">
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">Scan QR code ini untuk memverifikasi dokumen</p>
                                </div>
                                @endif

                                @if($dokumen->file)
                                <div class="w-full text-center">
                                    <a href="{{ route('view.document', ['id' => $dokumen->id]) }}"
                                    class="inline-flex justify-center items-center px-6 py-3 w-full text-base font-medium text-white bg-blue-600 rounded-lg shadow-md transition duration-150 ease-in-out hover:bg-blue-700 hover:shadow-lg btn-primary"
                                    target="_blank">
                                        <svg class="mr-3 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Lihat Dokumen
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-8">
                        <!-- Header dengan Logo -->
                        <div class="flex justify-center items-center mb-8">
                            <div class="flex flex-shrink-0 justify-center items-center mr-4 w-16 h-16 text-2xl font-bold text-white bg-blue-600 rounded-full">
                                S<span class="text-yellow-400">X</span>
                            </div>
                            <div class="text-left">
                                <h1 class="text-2xl font-bold text-gray-900">Verifikasi Dokumen Digital</h1>
                                <p class="text-gray-600">SigniX - Sistem Pengelolaan Dokumen Digital</p>
                            </div>
                        </div>

                        <!-- Layout horizontal untuk konten error -->
                        <div class="flex flex-wrap justify-center items-center">
                            <!-- Bagian Ikon Error -->
                            <div class="flex justify-center px-4 w-full md:w-1/3">
                                <div class="inline-flex justify-center items-center mb-6 w-20 h-20 bg-red-100 rounded-full">
                                    <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Bagian Pesan Error -->
                            <div class="px-4 w-full md:w-2/3">
                                <h2 class="mb-3 text-2xl font-bold text-gray-900">Verifikasi Gagal</h2>
                                <p class="mb-4 text-lg text-gray-600">{{ $message ?? 'Dokumen tidak dapat diverifikasi atau tidak ditemukan dalam sistem.' }}</p>

                                <div class="p-4 text-amber-800 bg-amber-50 rounded-lg border border-amber-200">
                                    <p class="text-sm">Jika Anda yakin dokumen ini seharusnya valid, silakan periksa URL atau kode pengesahan yang Anda masukkan, atau hubungi penerbit dokumen.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="py-4 text-sm text-center text-gray-500">
        <p>&copy; {{ date('Y') }} SigniX - Sistem Pengelolaan Dokumen Digital</p>
        <p>Politeknik Negeri Indramayu</p>
    </div>

    <!-- Add Modal -->
    <div id="documentModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center p-4 bg-gray-100">
                <h3 class="text-lg font-semibold">Dokumen Preview</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <iframe id="pdf-viewer"></iframe>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="toast">
        <div class="flex items-center">
            <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span id="toast-message">Text copied!</span>
        </div>
    </div>

    <script>
        function showDocument(url) {
            const modal = document.getElementById('documentModal');
            const viewer = document.getElementById('pdf-viewer');

            // Set the source of the iframe
            viewer.src = url;

            // Show the modal
            modal.style.display = 'block';

            // Prevent body scrolling when modal is open
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('documentModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('documentModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                // Show toast notification
                showToast('Teks berhasil disalin!');
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                showToast('Gagal menyalin teks!', true);
            });
        }

        function showToast(message, isError = false) {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');

            // Set message
            toastMessage.textContent = message;

            // Set color based on status
            if (isError) {
                toast.style.backgroundColor = '#dc2626';
            } else {
                toast.style.backgroundColor = '#1e3a8a';
            }

            // Show toast
            toast.classList.add('show');

            // Hide toast after 3 seconds
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>
