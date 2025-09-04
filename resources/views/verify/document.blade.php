<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Dokumen</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <div class="min-h-screen flex flex-col items-center justify-center p-6">
        <div class="bg-white shadow-lg rounded-2xl w-full max-w-3xl p-6">

            <!-- Header -->
            <div class="flex items-center mb-6">
                <div class="w-14 h-14 flex items-center justify-center bg-blue-600 text-white font-bold rounded-full mr-4">
                    V
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Verifikasi Dokumen</h1>
                    <p class="text-gray-500 text-sm">Sistem Pengesahan Digital</p>
                </div>
            </div>

            @if($dokumen)
                <!-- Status -->
                <div class="flex items-center p-4 mb-6 rounded-lg
                    {{ $dokumen->status_dokumen === 'disahkan' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full mr-3
                        {{ $dokumen->status_dokumen === 'disahkan' ? 'bg-green-100' : 'bg-red-100' }}">
                        @if($dokumen->status_dokumen === 'disahkan')
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        @else
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        @endif
                    </div>
                    <div>
                        <p class="font-medium {{ $dokumen->status_dokumen === 'disahkan' ? 'text-green-700' : 'text-red-700' }}">
                            {{ ucfirst($dokumen->status_dokumen) }}
                        </p>
                        <p class="text-sm text-gray-600">
                            Tanggal verifikasi:
                            {{ $dokumen->tanggal_verifikasi ? \Carbon\Carbon::parse($dokumen->tanggal_verifikasi)->format('d M Y H:i') : '-' }}
                        </p>
                    </div>
                </div>

                <!-- Informasi Dokumen -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Nomor Surat</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->nomor_surat ?? '-' }}</p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Tanggal Pengajuan</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->created_at->format('d M Y') }}</p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Disahkan Oleh</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->kuwu->nama_kuwu ?? '-' }} <span class="text-xs text-gray-500">(Kuwu)</span></p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Dikelola Oleh</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->admin->namaAdmin ?? '-' }} <span class="text-xs text-gray-500">(Admin)</span></p>
                    </div>


                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Nama Pemohon</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->nama_pemohon ?? '-' }}</p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Tanggal Verifikasi</p>
                        <p class="font-semibold text-gray-800">
                            {{ $dokumen->tanggal_verifikasi ? $dokumen->tanggal_verifikasi->format('d M Y H:i') : '-' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-lg bg-gray-50">
                        <p class="text-sm text-gray-500">Kode Pengesahan</p>
                        <p class="font-semibold text-gray-800">{{ $dokumen->kode_pengesahan ?? '-' }}</p>
                    </div>


                </div>

                <!-- QR & Dokumen -->
                <div class="flex flex-col items-center">
                    @if($dokumen->qr_code_path)
                        <div class="mb-4">
                            <p class="text-sm text-gray-500 mb-2 text-center">QR Code Verifikasi</p>
                            <img src="{{ Storage::url($dokumen->qr_code_path) }}" alt="QR Code"
                                 class="w-40 h-40 object-contain border rounded-lg shadow">
                        </div>
                    @endif

                    @if($dokumen->file)
                        <a href="{{ route('view.document', $dokumen->id) }}" target="_blank"
                           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg shadow hover:bg-blue-700 transition">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Lihat Dokumen Asli
                        </a>
                    @endif
                </div>
            @else
                <!-- Error -->
                <div class="text-center py-10">
                    <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center rounded-full bg-red-100">
                        <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-800 mb-2">Dokumen Tidak Ditemukan</h2>
                    <p class="text-gray-600">QR Code tidak valid atau dokumen sudah dihapus.</p>
                </div>
            @endif

        </div>
    </div>

    <footer class="py-4 text-center text-gray-400 text-sm">
        &copy; {{ date('Y') }} Sistem Pengesahan Digital Desa
    </footer>
</body>
</html>
