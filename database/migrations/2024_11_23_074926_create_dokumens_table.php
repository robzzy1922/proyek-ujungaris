<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dokumens', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_surat');
            $table->string('jenis_surat');
            $table->string('nama_pemohon');
            $table->enum('status_dokumen', ['diajukan', 'disahkan', 'disetujui'])->default('diajukan');
            $table->string('file');
            $table->string('keterangan')->nullable();
            $table->date('tanggal_pengajuan');
            $table->unsignedBigInteger('id_ormawa');
            $table->unsignedBigInteger('id_dosen')->nullable(); // Allow null
            $table->unsignedBigInteger('id_kemahasiswaan')->nullable(); // Allow null
            $table->timestamps();

            $table->foreign('id_ormawa')->references('id')->on('ormawas');
            $table->foreign('id_dosen')->references('id')->on('dosen');
            $table->foreign('id_kemahasiswaan')->references('id')->on('kemahasiswaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumens');

    }
};
