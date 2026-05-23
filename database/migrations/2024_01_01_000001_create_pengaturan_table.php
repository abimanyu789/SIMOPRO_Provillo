<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel pengaturan (konfigurasi aplikasi).
 * Menyimpan informasi usaha, logo, dan konfigurasi sistem.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_usaha')->default('Provillo'); // Nama usaha
            $table->text('deskripsi_usaha')->nullable();        // Deskripsi usaha
            $table->text('alamat')->nullable();                 // Alamat usaha
            $table->string('no_hp')->nullable();               // Nomor HP usaha
            $table->string('email')->nullable();               // Email usaha
            $table->string('logo')->nullable();                // Path logo aplikasi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan');
    }
};
