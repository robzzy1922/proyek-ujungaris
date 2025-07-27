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
            $table->unsignedBigInteger('id_admin');
            $table->unsignedBigInteger('id_kuwu')->nullable(); // Allow null

            $table->timestamps();

            $table->foreign('id_admin')->references('id')->on('admin');
            $table->foreign('id_kuwu')->references('id')->on('kuwu');

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
