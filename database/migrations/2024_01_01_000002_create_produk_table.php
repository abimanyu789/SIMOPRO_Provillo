<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel produk (data master produk sepatu).
 * Menyimpan informasi produk yang dijual oleh Provillo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produk')->unique(); // Kode unik produk (auto-generate)
            $table->string('nama_produk');           // Nama model sepatu
            $table->string('kategori');              // Kategori produk (Formal, Casual, Sport, dll)
            $table->text('deskripsi')->nullable();   // Deskripsi produk
            $table->decimal('harga_jual', 15, 2)->default(0); // Harga jual (akan di-snapshot saat order)
            $table->string('foto')->nullable();      // Path foto produk
            $table->timestamps();
            $table->softDeletes();                   // Soft delete untuk keamanan data
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
