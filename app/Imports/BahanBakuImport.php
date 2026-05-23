<?php

namespace App\Imports;

use App\Models\BahanBaku;
use App\Models\StokBahanBaku;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BahanBakuImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?BahanBaku
    {
        return DB::transaction(function () use ($row) {
            $prefix = 'BB-' . date('Ym') . '-';
            $last = BahanBaku::withTrashed()->where('kode_bahan', 'like', $prefix . '%')->orderByDesc('kode_bahan')->first();
            $number = $last ? (int) substr($last->kode_bahan, -4) + 1 : 1;
            $kode = $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);

            $bahan = BahanBaku::create([
                'kode_bahan'  => $kode,
                'nama_bahan'  => $row['nama_bahan'],
                'kategori'    => $row['kategori'],
                'satuan'      => $row['satuan'] ?? 'pcs',
                'harga_beli'  => $row['harga_beli'] ?? 0,
                'deskripsi'   => $row['deskripsi'] ?? null,
            ]);

            StokBahanBaku::create(['bahan_baku_id' => $bahan->id, 'jumlah_stok' => 0, 'stok_minimum' => 10, 'satuan' => $bahan->satuan]);

            return $bahan;
        });
    }
}
