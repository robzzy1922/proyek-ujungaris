<?php

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('kuwu', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kuwu', 50);
            $table->string('nip', 20)->unique();
            $table->string('email', 50)->unique();
            $table->string('no_hp', 20);
            $table->string('password');
            $table->string('profile')->nullable();
            $table->rememberToken(); // Tambahkan ini
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kuwu');
    }
};
