<?php

namespace App\Exports;

use App\Models\Produk;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export data produk ke format Excel.
 */
class ProdukExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Produk::with('stok')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Kategori',
            'Harga Jual',
            'Stok Saat Ini',
            'Stok Minimum',
            'Status Stok',
            'Deskripsi',
        ];
    }

    public function map($produk): array
    {
        return [
            $produk->kode_produk,
            $produk->nama_produk,
            $produk->kategori,
            $produk->harga_jual,
            $produk->stok?->jumlah_stok ?? 0,
            $produk->stok?->stok_minimum ?? 0,
            $produk->stok?->status_stok ?? '-',
            $produk->deskripsi ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
