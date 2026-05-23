<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel pesanan dan detail_pesanan.
 * 
 * PENTING: Harga produk di-snapshot saat pesanan dibuat.
 * Perubahan harga master tidak mempengaruhi pesanan yang sudah ada.
 * 
 * Workflow status pesanan:
 * pending → diproses → produksi → selesai → closed
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tabel header pesanan
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pesanan')->unique(); // Kode order (PO-YYYYMM-XXXX)
            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete(); // Cegah hapus customer jika ada pesanan
            $table->date('tanggal_pesanan');           // Tanggal pembuatan pesanan
            $table->date('tanggal_kirim')->nullable(); // Tanggal estimasi pengiriman
            $table->enum('status', [
                'pending',
                'diproses',
                'produksi',
                'selesai',
                'closed'
            ])->default('pending'); // Status workflow pesanan
            $table->decimal('total_harga', 15, 2)->default(0); // Total nilai pesanan
            $table->text('catatan')->nullable(); // Catatan pesanan
            $table->timestamps();
            $table->softDeletes();
        });

        // Tabel detail item pesanan (snapshot harga)
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')
                ->constrained('pesanan')
                ->cascadeOnDelete(); // Hapus detail jika pesanan dihapus
            $table->foreignId('produk_id')
                ->constrained('produk')
                ->restrictOnDelete(); // Cegah hapus produk jika ada detail pesanan

            // SNAPSHOT: Data produk saat pesanan dibuat - tidak berubah walaupun master diubah
            $table->string('nama_produk_snapshot'); // Nama produk saat order
            $table->decimal('harga_satuan_snapshot', 15, 2); // Harga saat order dibuat

            $table->integer('jumlah');              // Jumlah yang dipesan
            $table->integer('jumlah_terkirim')->default(0); // Jumlah yang sudah dikirim
            $table->string('ukuran')->nullable();   // Ukuran sepatu
            $table->string('warna')->nullable();    // Warna sepatu
            $table->decimal('subtotal', 15, 2);    // subtotal = harga_satuan_snapshot × jumlah
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan');
    }
};
