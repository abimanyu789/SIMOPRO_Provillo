<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel stok_produk dan stok_bahan_baku.
 * 
 * Logika status stok:
 * - Tersedia: jumlah_stok >= stok_minimum
 * - Menipis: 0 < jumlah_stok < stok_minimum
 * - Habis: jumlah_stok = 0
 */
return new class extends Migration
{
    public function up(): void
    {
        // Stok produk jadi (sepatu)
        Schema::create('stok_produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')
                ->unique() // Satu produk hanya punya satu record stok
                ->constrained('produk')
                ->cascadeOnDelete(); // Hapus stok jika produk dihapus
            $table->integer('jumlah_stok')->default(0); // Jumlah stok saat ini
            $table->integer('stok_minimum')->default(10); // Batas minimum stok
            $table->timestamps();
        });

        // Stok bahan baku produksi
        Schema::create('stok_bahan_baku', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bahan_baku_id')
                ->unique() // Satu bahan hanya punya satu record stok
                ->constrained('bahan_baku')
                ->cascadeOnDelete(); // Hapus stok jika bahan dihapus
            $table->integer('jumlah_stok')->default(0); // Jumlah stok saat ini
            $table->integer('stok_minimum')->default(10); // Batas minimum stok
            $table->string('satuan')->default('pcs'); // Satuan bahan baku
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stok_bahan_baku');
        Schema::dropIfExists('stok_produk');
    }
};
