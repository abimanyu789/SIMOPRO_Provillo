<?php

namespace App\Imports;

use App\Models\Produk;
use App\Models\StokProduk;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Import data produk dari Excel.
 * Format kolom: nama_produk, kategori, harga_jual, deskripsi
 */
class ProdukImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row): ?Produk
    {
        return DB::transaction(function () use ($row) {
            $prefix = 'PRD-' . date('Ym') . '-';
            $last = Produk::withTrashed()->where('kode_produk', 'like', $prefix . '%')->orderByDesc('kode_produk')->first();
            $number = $last ? (int) substr($last->kode_produk, -4) + 1 : 1;
            $kode = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

            $produk = Produk::create([
                'kode_produk' => $kode,
                'nama_produk' => $row['nama_produk'],
                'kategori'    => $row['kategori'],
                'harga_jual'  => $row['harga_jual'] ?? 0,
                'deskripsi'   => $row['deskripsi'] ?? null,
            ]);

            StokProduk::create(['produk_id' => $produk->id, 'jumlah_stok' => 0, 'stok_minimum' => 10]);

            return $produk;
        });
    }

    public function rules(): array
    {
        return [
            'nama_produk' => 'required|string',
            'kategori'    => 'required|string',
            'harga_jual'  => 'required|numeric',
        ];
    }
}
