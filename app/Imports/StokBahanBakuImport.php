<?php

namespace App\Imports;

use App\Models\StokBahanBaku;
use App\Models\BahanBaku;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StokBahanBakuImport implements ToModel, WithHeadingRow
{
    public function model(array $row): ?StokBahanBaku
    {
        $bahan = BahanBaku::where('kode_bahan', $row['kode_bahan'])->first();
        if (!$bahan) return null;

        return StokBahanBaku::updateOrCreate(
            ['bahan_baku_id' => $bahan->id],
            ['jumlah_stok' => $row['jumlah_stok'] ?? 0, 'stok_minimum' => $row['stok_minimum'] ?? 10, 'satuan' => $row['satuan'] ?? 'pcs']
        );
    }
}
