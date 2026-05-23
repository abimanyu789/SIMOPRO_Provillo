<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel produksi (log produksi harian karyawan).
 * Setiap record = 1 sesi produksi karyawan untuk pesanan tertentu.
 * Total upah dihitung otomatis: upah_per_item × jumlah_produksi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produksi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_produksi')->unique(); // Kode produksi (PROD-YYYYMM-XXXX)
            $table->foreignId('karyawan_id')
                ->constrained('karyawan')
                ->restrictOnDelete(); // Cegah hapus karyawan jika ada produksi
            $table->foreignId('pesanan_id')
                ->constrained('pesanan')
                ->restrictOnDelete(); // Cegah hapus pesanan jika ada produksi terkait
            $table->date('tanggal_produksi');           // Tanggal produksi dilakukan
            $table->integer('jumlah_produksi');         // Jumlah pasang sepatu yang diproduksi
            $table->decimal('upah_per_item', 15, 2);   // Upah per pasang sepatu
            $table->decimal('total_upah', 15, 2);      // total_upah = upah_per_item × jumlah_produksi
            $table->text('catatan')->nullable();         // Catatan produksi
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi');
    }
};
