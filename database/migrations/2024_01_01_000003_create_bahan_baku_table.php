<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel bahan_baku (data master bahan baku).
 * Menyimpan informasi bahan baku produksi sepatu Provillo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->string('kode_bahan')->unique(); // Kode unik bahan baku
            $table->string('nama_bahan');           // Nama bahan baku
            $table->string('kategori');             // Kategori (Kulit, Karet, Benang, dll)
            $table->string('satuan')->default('pcs'); // Satuan (pcs, meter, kg, dll)
            $table->decimal('harga_beli', 15, 2)->default(0); // Harga beli per satuan
            $table->text('deskripsi')->nullable();  // Deskripsi bahan baku
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bahan_baku');
    }
};
