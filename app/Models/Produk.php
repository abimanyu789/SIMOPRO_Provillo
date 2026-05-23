<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Produk - Data Master Produk Sepatu Provillo.
 *
 * @property int $id
 * @property string $kode_produk
 * @property string $nama_produk
 * @property string $kategori
 * @property string|null $deskripsi
 * @property float $harga_jual
 * @property string|null $foto
 */
class Produk extends Model
{
    use SoftDeletes;

    protected $table = 'produk';

    protected $fillable = [
        'kode_produk',
        'nama_produk',
        'kategori',
        'deskripsi',
        'harga_jual',
        'foto',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
    ];

    /**
     * Relasi ke stok produk (one-to-one).
     */
    public function stok(): HasOne
    {
        return $this->hasOne(StokProduk::class, 'produk_id');
    }

    /**
     * Relasi ke detail pesanan.
     */
    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'produk_id');
    }

    /**
     * Accessor: URL foto produk.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(storage_path('app/public/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }
        return asset('images/default-product.png');
    }

    /**
     * Accessor: Harga jual terformat (Rupiah).
     */
    public function getHargaFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->harga_jual, 0, ',', '.');
    }
}
