<?php

namespace App\Exports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KaryawanExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function collection()
    {
        return Karyawan::all();
    }

    public function headings(): array
    {
        return ['Kode', 'Nama', 'Posisi', 'Divisi', 'Tanggal Lahir', 'No HP', 'Email', 'Tanggal Bergabung', 'Status'];
    }

    public function map($k): array
    {
        return [
            $k->kode_karyawan,
            $k->nama,
            $k->posisi,
            $k->divisi,
            $k->tanggal_lahir?->format('Y-m-d'),
            $k->no_hp,
            $k->email,
            $k->tanggal_bergabung?->format('Y-m-d'),
            $k->status,
        ];
    }
}
