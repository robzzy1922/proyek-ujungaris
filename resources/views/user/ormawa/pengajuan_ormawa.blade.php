@extends('layouts.app_admin')
@section('title', 'Formulir Pengajuan')
@section('content')
    <div class="container flex-grow px-4 mx-auto mt-4 max-w-3xl sm:px-6 sm:mt-8">
        <h1 class="mb-4 text-xl font-bold text-center sm:mb-6 sm:text-2xl sm:text-left">FORMULIR PENGAJUAN</h1>

        @if(session('success'))
            <div id="alert-success" class="relative p-3 mb-4 text-sm text-green-700 bg-green-100 rounded border border-green-400 transition-all duration-300 transform sm:p-4 sm:mb-6 sm:text-base" role="alert">
                <div class="flex items-center">
                    <svg class="mr-2 w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <span class="font-bold">Success! </span>
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
                <div class="absolute bottom-0 left-0 w-full h-1 bg-green-200">
                    <div id="progress-bar" class="h-1 bg-green-600 transition-all duration-[5000ms] ease-linear w-full"></div>
                </div>
            </div>

            <script>
                const alert = document.getElementById('alert-success');
                const progressBar = document.getElementById('progress-bar');
                progressBar.getBoundingClientRect();
                progressBar.style.width = '0%';
                setTimeout(() => {
                    alert.style.transform = 'translateX(100%)';
                    alert.style.opacity = '0';
                    setTimeout(() => {
                        alert.remove();
                    }, 300);
                }, 5000);
            </script>
        @endif

        @if ($errors->any())
            <div class="p-4 mb-4 text-red-700 bg-red-100 rounded border border-red-400">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('ormawa.pengajuan.store') }}" method="POST" enctype="multipart/form-data" class="w-full" id="pengajuanForm">
            @csrf
            <div class="space-y-3 sm:space-y-4">
                <div>
                    <label for="nomor_surat" class="block mb-1 text-sm font-medium sm:text-base">Nomor Surat</label>
                    <input type="text" id="nomor_surat" name="nomor_surat" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base" required>
                </div>

                <div>
                    <label for="jenis_surat" class="block mb-1 text-sm font-medium sm:text-base">Jenis Surat</label>
                    <input type="text" id="jenis_surat" name="jenis_surat" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base" required>
                </div>

                <div>
                    <label for="nama_pemohon" class="block mb-1 text-sm font-medium sm:text-base">Nama Pemohon</label>
                    <input type="text" id="nama_pemohon" name="nama_pemohon" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base" required>
                </div>

                <div>
                    <label for="nama_pengaju" class="block mb-1 text-sm font-medium sm:text-base">Nama Pengaju</label>
                    <input type="text" id="nama_pengaju" name="nama_pengaju" class="px-3 py-2 w-full text-sm bg-gray-200 rounded-md border sm:text-base" value="{{ $ormawa->namaMahasiswa }}" readonly required>
                </div>

                <div>
                    <input type="hidden" id="tujuan_pengajuan" name="tujuan_pengajuan" value="dosen">
                </div>

                <div id="dosen_section">
                    <label for="kepada_tujuan" class="block mb-1 text-sm font-medium sm:text-base">Pilih Kuwu</label>
                    <select id="kepada_tujuan" name="kepada_tujuan" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base" required>
                        <option value="">Pilih Kuwu</option>
                        @foreach($dosenList as $dosen)
                            <option value="{{ $dosen->id }}">{{ $dosen->nama_dosen }}</option>
                        @endforeach
                    </select>
                    @error('kepada_tujuan')
                        <span class="text-sm text-red-500">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="unggah_dokumen" class="block mb-1 text-sm font-medium sm:text-base">Unggah Dokumen</label>
                    <input type="file" id="unggah_dokumen" name="unggah_dokumen" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base" required>
                </div>

                <div>
                    <label for="catatan" class="block mb-1 text-sm font-medium sm:text-base">Catatan (opsional)</label>
                    <textarea id="catatan" name="catatan" rows="4" class="px-3 py-2 w-full text-sm rounded-md border sm:text-base"></textarea>
                </div>

                <div class="mt-4 sm:mt-6">
                    <button type="submit" class="px-4 py-2 w-full text-sm text-white bg-blue-500 rounded-md sm:w-auto sm:text-base hover:bg-blue-600">Ajukan</button>
                </div>
            </div>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('pengajuanForm');
        const kepada_tujuan = document.getElementById('kepada_tujuan');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Form submitted');

            if (!kepada_tujuan.value) {
                alert('Silakan pilih kuwu tujuan');
                return;
            }

            console.log('Submitting form...');
            this.submit();
        });
    });
    </script>
@endsection
