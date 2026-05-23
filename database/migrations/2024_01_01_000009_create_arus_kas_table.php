<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel arus_kas (cash flow).
 * Mencatat semua transaksi keuangan masuk dan keluar.
 * Mendukung polymorphic reference ke model lain (pesanan, produksi, dll).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arus_kas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_transaksi')->unique(); // Kode transaksi (AK-YYYYMM-XXXX)
            $table->enum('jenis', ['pemasukan', 'pengeluaran']); // Jenis transaksi
            $table->string('kategori');                 // Kategori (Penjualan, Upah, Pembelian Bahan, dll)
            $table->string('deskripsi');               // Deskripsi transaksi
            $table->decimal('jumlah', 15, 2);          // Jumlah transaksi
            $table->date('tanggal');                    // Tanggal transaksi

            // Polymorphic relationship untuk referensi ke sumber transaksi
            $table->nullableMorphs('referensi'); // referensi_id, referensi_type

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arus_kas');
    }
};
