<?php

namespace App\Imports;

use App\Models\StokProduk;
use App\Models\Produk;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StokProdukImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?StokProduk
    {
        $produk = Produk::where('kode_produk', $row['kode_produk'])->first();
        if (!$produk) return null;

        return StokProduk::updateOrCreate(
            ['produk_id' => $produk->id],
            ['jumlah_stok' => $row['jumlah_stok'] ?? 0, 'stok_minimum' => $row['stok_minimum'] ?? 10]
        );
    }
}
