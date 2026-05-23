<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model BahanBaku - Data Master Bahan Baku Produksi.
 *
 * @property int $id
 * @property string $kode_bahan
 * @property string $nama_bahan
 * @property string $kategori
 * @property string $satuan
 * @property float $harga_beli
 * @property string|null $deskripsi
 */
class BahanBaku extends Model
{
    use SoftDeletes;

    protected $table = 'bahan_baku';

    protected $fillable = [
        'kode_bahan',
        'nama_bahan',
        'kategori',
        'satuan',
        'harga_beli',
        'deskripsi',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
    ];

    /**
     * Relasi ke stok bahan baku (one-to-one).
     */
    public function stok(): HasOne
    {
        return $this->hasOne(StokBahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Accessor: Harga beli terformat.
     */
    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_beli, 0, ',', '.');
    }
}
