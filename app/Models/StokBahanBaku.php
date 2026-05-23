<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model StokBahanBaku - Manajemen Stok Bahan Baku.
 * Logika status sama dengan StokProduk.
 */
class StokBahanBaku extends Model
{
    protected $table = 'stok_bahan_baku';

    protected $fillable = [
        'bahan_baku_id',
        'jumlah_stok',
        'stok_minimum',
        'satuan',
    ];

    /**
     * Relasi ke bahan baku.
     */
    public function bahanBaku(): BelongsTo
    {
        return $this->belongsTo(BahanBaku::class, 'bahan_baku_id');
    }

    /**
     * Accessor: Status stok.
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
     * Accessor: CSS class badge status.
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
     * Scope: Stok menipis.
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
