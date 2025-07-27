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
        Schema::create('tanda_qrs', function (Blueprint $table) {
            $table->id();
            $table->string('data_qr');
            $table->date('tanggal_pembuatan');
            $table->foreignId('id_dokumen')->constrained('dokumens')->onDelete('cascade');
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
        Schema::dropIfExists('tanda_qrs');
    }
};
