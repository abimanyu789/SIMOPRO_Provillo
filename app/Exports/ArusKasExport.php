<?php

namespace App\Exports;

use App\Models\ArusKas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ArusKasExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection() { return ArusKas::orderBy('tanggal', 'desc')->get(); }
    public function headings(): array { return ['Kode', 'Jenis', 'Kategori', 'Deskripsi', 'Jumlah', 'Tanggal']; }
    public function map($a): array { return [$a->kode_transaksi, $a->jenis, $a->kategori, $a->deskripsi, $a->jumlah, $a->tanggal?->format('Y-m-d')]; }
}
