<?php

namespace Database\Seeders;

use App\Models\ArusKas;
use App\Models\BahanBaku;
use App\Models\Customer;
use App\Models\DetailPesanan;
use App\Models\Karyawan;
use App\Models\Pengaturan;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\Produksi;
use App\Models\StokBahanBaku;
use App\Models\StokProduk;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DatabaseSeeder - Seeder utama SIMOPRO.
 * Mengisi data awal yang dibutuhkan untuk demonstrasi sistem.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Bersihkan tabel (urut dari yang bergantung)
        ArusKas::truncate();
        Produksi::truncate();
        DetailPesanan::truncate();
        Pesanan::truncate();
        StokProduk::truncate();
        StokBahanBaku::truncate();
        Produk::truncate();
        BahanBaku::truncate();
        Karyawan::truncate();
        Customer::truncate();
        User::truncate();
        Pengaturan::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // ---- ADMIN USER ----
        User::create([
            'name'     => 'Admin Provillo',
            'email'    => 'admin@provillo.com',
            'password' => Hash::make('password123'),
            'jabatan'  => 'Owner',
        ]);

        // ---- PENGATURAN USAHA ----
        Pengaturan::create([
            'nama_usaha'      => 'Provillo',
            'deskripsi_usaha' => 'Produsen Sepatu Berkualitas',
            'alamat'          => 'Jl. Industri No. 12, Bandung, Jawa Barat',
            'no_hp'           => '081234567890',
            'email'           => 'provillo@gmail.com',
        ]);

        // ---- PRODUK ----
        $produkData = [
            ['kode_produk' => 'PRD-202501-0001', 'nama_produk' => 'Sepatu Formal Kulit Classic', 'kategori' => 'Formal', 'harga_jual' => 350000, 'deskripsi' => 'Sepatu formal berbahan kulit asli'],
            ['kode_produk' => 'PRD-202501-0002', 'nama_produk' => 'Sepatu Casual Sneakers Provillo', 'kategori' => 'Casual', 'harga_jual' => 275000, 'deskripsi' => 'Sneakers kasual nyaman sehari-hari'],
            ['kode_produk' => 'PRD-202501-0003', 'nama_produk' => 'Sepatu Sport Running', 'kategori' => 'Sport', 'harga_jual' => 320000, 'deskripsi' => 'Sepatu lari dengan sol ringan'],
            ['kode_produk' => 'PRD-202501-0004', 'nama_produk' => 'Sandal Kulit Premium', 'kategori' => 'Sandal', 'harga_jual' => 180000, 'deskripsi' => 'Sandal kulit mewah'],
            ['kode_produk' => 'PRD-202501-0005', 'nama_produk' => 'Sepatu Boots Tinggi', 'kategori' => 'Boots', 'harga_jual' => 450000, 'deskripsi' => 'Boots kulit tinggi untuk pria'],
        ];

        foreach ($produkData as $data) {
            $produk = Produk::create($data);
            StokProduk::create([
                'produk_id'    => $produk->id,
                'jumlah_stok'  => rand(5, 50),
                'stok_minimum' => 10,
            ]);
        }

        // ---- BAHAN BAKU ----
        $bahanData = [
            ['kode_bahan' => 'BB-202501-0001', 'nama_bahan' => 'Kulit Sapi Grade A', 'kategori' => 'Kulit', 'satuan' => 'meter', 'harga_beli' => 125000],
            ['kode_bahan' => 'BB-202501-0002', 'nama_bahan' => 'Kulit Sintetis', 'kategori' => 'Kulit', 'satuan' => 'meter', 'harga_beli' => 45000],
            ['kode_bahan' => 'BB-202501-0003', 'nama_bahan' => 'Sol Karet EVA', 'kategori' => 'Sol', 'satuan' => 'pcs', 'harga_beli' => 25000],
            ['kode_bahan' => 'BB-202501-0004', 'nama_bahan' => 'Benang Nilon', 'kategori' => 'Benang', 'satuan' => 'gulung', 'harga_beli' => 15000],
            ['kode_bahan' => 'BB-202501-0005', 'nama_bahan' => 'Lem Sepatu Super', 'kategori' => 'Perekat', 'satuan' => 'kaleng', 'harga_beli' => 35000],
        ];

        foreach ($bahanData as $data) {
            $bahan = BahanBaku::create($data);
            StokBahanBaku::create([
                'bahan_baku_id' => $bahan->id,
                'jumlah_stok'   => rand(5, 100),
                'stok_minimum'  => 20,
                'satuan'        => $data['satuan'],
            ]);
        }

        // ---- KARYAWAN ----
        $karyawanData = [
            ['kode_karyawan' => 'KRY-202501-0001', 'nama' => 'Budi Santoso', 'posisi' => 'Penjahit', 'divisi' => 'Produksi', 'no_hp' => '081111111111', 'status' => 'aktif'],
            ['kode_karyawan' => 'KRY-202501-0002', 'nama' => 'Siti Rahayu', 'posisi' => 'Finishing', 'divisi' => 'Produksi', 'no_hp' => '082222222222', 'status' => 'aktif'],
            ['kode_karyawan' => 'KRY-202501-0003', 'nama' => 'Ahmad Fauzi', 'posisi' => 'Cutting', 'divisi' => 'Produksi', 'no_hp' => '083333333333', 'status' => 'aktif'],
            ['kode_karyawan' => 'KRY-202501-0004', 'nama' => 'Dewi Lestari', 'posisi' => 'Quality Control', 'divisi' => 'QC', 'no_hp' => '084444444444', 'status' => 'aktif'],
        ];

        foreach ($karyawanData as $data) {
            Karyawan::create($data);
        }

        // ---- CUSTOMER ----
        $customerData = [
            ['kode_customer' => 'CST-202501-0001', 'nama' => 'Toko Sepatu Maju', 'no_hp' => '021-111222', 'kota' => 'Jakarta', 'provinsi' => 'DKI Jakarta'],
            ['kode_customer' => 'CST-202501-0002', 'nama' => 'Grosir Alas Kaki Bandung', 'no_hp' => '022-333444', 'kota' => 'Bandung', 'provinsi' => 'Jawa Barat'],
            ['kode_customer' => 'CST-202501-0003', 'nama' => 'Toko Fashion Surabaya', 'no_hp' => '031-555666', 'kota' => 'Surabaya', 'provinsi' => 'Jawa Timur'],
        ];

        foreach ($customerData as $data) {
            Customer::create($data);
        }

        // ---- PESANAN CONTOH ----
        $pesanan1 = Pesanan::create([
            'kode_pesanan'    => 'PO-202501-0001',
            'customer_id'     => 1,
            'tanggal_pesanan' => now()->subDays(10),
            'tanggal_kirim'   => now()->addDays(5),
            'status'          => 'produksi',
            'total_harga'     => 3500000,
            'catatan'         => 'Pesanan urgent sebelum lebaran',
        ]);

        DetailPesanan::create([
            'pesanan_id'            => $pesanan1->id,
            'produk_id'             => 1,
            'nama_produk_snapshot'  => 'Sepatu Formal Kulit Classic',
            'harga_satuan_snapshot' => 350000,
            'jumlah'                => 10,
            'jumlah_terkirim'       => 0,
            'ukuran'                => '42',
            'warna'                 => 'Hitam',
            'subtotal'              => 3500000,
        ]);

        // Arus kas untuk pesanan
        ArusKas::create([
            'kode_transaksi'  => 'AK-202501-0001',
            'jenis'           => 'pemasukan',
            'kategori'        => 'Penjualan',
            'deskripsi'       => 'Pesanan #PO-202501-0001',
            'jumlah'          => 3500000,
            'tanggal'         => now()->subDays(10),
            'referensi_id'    => $pesanan1->id,
            'referensi_type'  => Pesanan::class,
        ]);

        // Produksi contoh
        $produksi1 = Produksi::create([
            'kode_produksi'    => 'PROD-202501-0001',
            'karyawan_id'      => 1,
            'pesanan_id'       => $pesanan1->id,
            'tanggal_produksi' => now()->subDays(5),
            'jumlah_produksi'  => 5,
            'upah_per_item'    => 15000,
            'total_upah'       => 75000,
            'catatan'          => 'Produksi batch pertama',
        ]);

        ArusKas::create([
            'kode_transaksi'  => 'AK-202501-0002',
            'jenis'           => 'pengeluaran',
            'kategori'        => 'Upah Produksi',
            'deskripsi'       => 'Upah Budi Santoso - PROD-202501-0001',
            'jumlah'          => 75000,
            'tanggal'         => now()->subDays(5),
            'referensi_id'    => $produksi1->id,
            'referensi_type'  => Produksi::class,
        ]);

        // Pengeluaran bahan baku
        ArusKas::create([
            'kode_transaksi' => 'AK-202501-0003',
            'jenis'          => 'pengeluaran',
            'kategori'       => 'Pembelian Bahan Baku',
            'deskripsi'      => 'Pembelian kulit sapi 10 meter',
            'jumlah'         => 1250000,
            'tanggal'        => now()->subDays(15),
        ]);

        $this->command->info('✅ Seeder SIMOPRO berhasil! Login: admin@provillo.com / password123');
    }
}
