<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model StokProduk - Manajemen Stok Produk Jadi.
 *
 * Status stok:
 * - tersedia: jumlah_stok >= stok_minimum
 * - menipis: 0 < jumlah_stok < stok_minimum
 * - habis: jumlah_stok == 0
 */
class StokProduk extends Model
{
    protected $table = 'stok_produk';

    protected $fillable = [
        'produk_id',
        'jumlah_stok',
        'stok_minimum',
    ];

    /**
     * Relasi ke produk.
     */
    public function produk(): BelongsTo
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }

    /**
     * Accessor: Status stok berdasarkan jumlah dan minimum.
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->jumlah_stok <= 0) {
            return 'habis';
        }
        if ($this->jumlah_stok < $this->stok_minimum) {
            return 'menipis';
        }
        return 'tersedia';
    }

    /**
     * Accessor: Warna badge status stok.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status_stok) {
            'tersedia' => 'badge-success',
            'menipis'  => 'badge-warning',
            'habis'    => 'badge-danger',
            default    => 'badge-secondary',
        };
    }

    /**
     * Scope: Stok yang menipis (untuk alert dashboard).
     */
    public function scopeMenipis($query)
    {
        return $query->where('jumlah_stok', '>', 0)
            ->whereColumn('jumlah_stok', '<', 'stok_minimum');
    }

    /**
     * Scope: Stok habis.
     */
    public function scopeHabis($query)
    {
        return $query->where('jumlah_stok', '<=', 0);
    }
}
