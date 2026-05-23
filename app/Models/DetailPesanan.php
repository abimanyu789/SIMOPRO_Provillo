<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model DetailPesanan - Item baris pesanan dengan snapshot harga.
 *
 * PENTING: nama_produk_snapshot dan harga_satuan_snapshot adalah
 * snapshot nilai saat pesanan dibuat. Tidak berubah walaupun
 * data master produk diubah kemudian.
 */
class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';

    protected $fillable = [
        'pesanan_id',
        'produk_id',
        'nama_produk_snapshot',
        'harga_satuan_snapshot',
        'jumlah',
        'jumlah_terkirim',
        'ukuran',
        'warna',
        'subtotal',
    ];

    protected $casts = [
        'harga_satuan_snapshot' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Relasi ke pesanan header.
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Relasi ke produk (untuk referensi, bukan harga aktual).
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Accessor: Sisa jumlah yang belum dikirim.
     */
    public function getSisaKirimAttribute(): int
    {
        return max(0, $this->jumlah - $this->jumlah_terkirim);
    }
}
