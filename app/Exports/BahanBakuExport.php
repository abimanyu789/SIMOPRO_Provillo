<?php

namespace App\Exports;

use App\Models\BahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class BahanBakuExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return BahanBaku::with('stok')->get();
    }

    public function headings(): array
    {
        return ['Kode Bahan', 'Nama Bahan', 'Kategori', 'Satuan', 'Harga Beli', 'Stok', 'Stok Min', 'Deskripsi'];
    }

    public function map($bahan): array
    {
        return [
            $bahan->kode_bahan,
            $bahan->nama_bahan,
            $bahan->kategori,
            $bahan->satuan,
            $bahan->harga_beli,
            $bahan->stok?->jumlah_stok ?? 0,
            $bahan->stok?->stok_minimum ?? 0,
            $bahan->deskripsi ?? '-',
        ];
    }
}
