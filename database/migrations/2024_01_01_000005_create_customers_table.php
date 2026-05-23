<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel customers (data master pelanggan).
 * Menyimpan data pelanggan yang memesan produk Provillo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('kode_customer')->unique(); // Kode unik pelanggan
            $table->string('nama');                    // Nama pelanggan / toko
            $table->string('no_hp')->nullable();       // Nomor handphone
            $table->string('email')->nullable();       // Email pelanggan
            $table->text('alamat')->nullable();        // Alamat pengiriman
            $table->string('kota')->nullable();        // Kota
            $table->string('provinsi')->nullable();    // Provinsi
            $table->string('kode_pos')->nullable();    // Kode pos
            $table->text('deskripsi')->nullable();     // Catatan tambahan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
