<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model Produksi - Log Produksi Harian Karyawan.
 * Total upah = upah_per_item × jumlah_produksi (dihitung otomatis).
 */
class Produksi extends Model
{
    use SoftDeletes;

    protected $table = 'produksi';

    protected $fillable = [
        'kode_produksi',
        'karyawan_id',
        'pesanan_id',
        'tanggal_produksi',
        'jumlah_produksi',
        'upah_per_item',
        'total_upah',
        'catatan',
    ];

    protected $casts = [
        'tanggal_produksi' => 'date',
        'upah_per_item' => 'decimal:2',
        'total_upah' => 'decimal:2',
    ];

    /**
     * Relasi ke karyawan.
     */
    public function karyawan(): BelongsTo
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    /**
     * Relasi ke pesanan.
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * Accessor: Format total upah.
     */
    public function getTotalUpahFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_upah, 0, ',', '.');
    }
}
