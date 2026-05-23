<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration untuk tabel karyawan (data master karyawan).
 * Menyimpan data karyawan produksi Provillo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('karyawan', function (Blueprint $table) {
            $table->id();
            $table->string('kode_karyawan')->unique(); // Kode unik karyawan
            $table->string('nama');                    // Nama lengkap karyawan
            $table->date('tanggal_lahir')->nullable(); // Tanggal lahir
            $table->string('posisi');                  // Posisi/jabatan (Penjahit, Finishing, dll)
            $table->string('divisi')->nullable();      // Divisi karyawan
            $table->string('no_hp')->nullable();       // Nomor handphone
            $table->string('email')->nullable();       // Email karyawan
            $table->text('alamat')->nullable();        // Alamat karyawan
            $table->date('tanggal_bergabung')->nullable(); // Tanggal bergabung
            $table->string('no_rekening')->nullable(); // Nomor rekening bank
            $table->string('foto')->nullable();        // Path foto karyawan
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif'); // Status karyawan
            $table->text('deskripsi')->nullable();     // Keterangan tambahan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('karyawan');
    }
};
