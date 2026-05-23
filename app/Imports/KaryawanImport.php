<?php

namespace App\Imports;

use App\Models\Karyawan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KaryawanImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?Karyawan
    {
        $prefix = 'KRY-' . date('Ym') . '-';
        $last = Karyawan::withTrashed()->where('kode_karyawan', 'like', $prefix . '%')->orderByDesc('kode_karyawan')->first();
        $number = $last ? (int) substr($last->kode_karyawan, -4) + 1 : 1;
        $kode = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

        return new Karyawan([
            'kode_karyawan' => $kode,
            'nama'          => $row['nama'],
            'posisi'        => $row['posisi'],
            'divisi'        => $row['divisi'] ?? null,
            'no_hp'         => $row['no_hp'] ?? null,
            'email'         => $row['email'] ?? null,
            'status'        => $row['status'] ?? 'aktif',
        ]);
    }
}
