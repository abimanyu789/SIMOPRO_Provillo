<?php

namespace App\Exports;

use App\Models\Produksi;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProduksiExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection() { return Produksi::with(['karyawan', 'pesanan'])->get(); }
    public function headings(): array { return ['Kode', 'Karyawan', 'Pesanan', 'Tanggal', 'Jumlah Produksi', 'Upah/Item', 'Total Upah', 'Catatan']; }
    public function map($p): array {
        return [
            $p->kode_produksi,
            $p->karyawan?->nama,
            $p->pesanan?->kode_pesanan,
            $p->tanggal_produksi?->format('Y-m-d'),
            $p->jumlah_produksi,
            $p->upah_per_item,
            $p->total_upah,
            $p->catatan,
        ];
    }
}
