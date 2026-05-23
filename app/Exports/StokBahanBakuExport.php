<?php

namespace App\Exports;

use App\Models\StokBahanBaku;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StokBahanBakuExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection() { return StokBahanBaku::with('bahanBaku')->get(); }
    public function headings(): array { return ['Kode Bahan', 'Nama Bahan', 'Kategori', 'Satuan', 'Jumlah Stok', 'Stok Minimum', 'Status']; }
    public function map($s): array {
        return [
            $s->bahanBaku?->kode_bahan,
            $s->bahanBaku?->nama_bahan,
            $s->bahanBaku?->kategori,
            $s->satuan,
            $s->jumlah_stok,
            $s->stok_minimum,
            $s->status_stok,
        ];
    }
}
