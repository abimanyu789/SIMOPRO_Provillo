<?php

namespace App\Exports;

use App\Models\StokProduk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StokProdukExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection() { return StokProduk::with('produk')->get(); }
    public function headings(): array { return ['Kode Produk', 'Nama Produk', 'Kategori', 'Jumlah Stok', 'Stok Minimum', 'Status']; }
    public function map($s): array {
        return [
            $s->produk?->kode_produk,
            $s->produk?->nama_produk,
            $s->produk?->kategori,
            $s->jumlah_stok,
            $s->stok_minimum,
            $s->status_stok,
        ];
    }
}
