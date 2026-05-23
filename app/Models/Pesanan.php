<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Pesanan - Manajemen Pesanan Provillo.
 *
 * Workflow status: pending → diproses → produksi → selesai → closed
 *
 * @property string $status pending|diproses|produksi|selesai|closed
 */
class Pesanan extends Model
{
    use SoftDeletes;

    protected $table = 'pesanan';

    protected $fillable = [
        'kode_pesanan',
        'customer_id',
        'tanggal_pesanan',
        'tanggal_kirim',
        'status',
        'total_harga',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pesanan' => 'date',
        'tanggal_kirim' => 'date',
        'total_harga' => 'decimal:2',
    ];

    /**
     * Relasi ke customer.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    /**
     * Relasi ke detail pesanan.
     */
    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id');
    }

    /**
     * Relasi ke produksi.
     */
    public function produksi(): HasMany
    {
        return $this->hasMany(Produksi::class, 'pesanan_id');
    }

    /**
     * Relasi polymorphic ke arus kas.
     */
    public function arusKas(): HasMany
    {
        return $this->hasMany(ArusKas::class, 'referensi_id')
            ->where('referensi_type', self::class);
    }

    /**
     * Accessor: Warna badge status pesanan.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'badge-warning',
            'diproses'  => 'badge-info',
            'produksi'  => 'badge-primary',
            'selesai'   => 'badge-success',
            'closed'    => 'badge-secondary',
            default     => 'badge-secondary',
        };
    }

    /**
     * Accessor: Label status dalam Bahasa Indonesia.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending'   => 'Pending',
            'diproses'  => 'Diproses',
            'produksi'  => 'Produksi',
            'selesai'   => 'Selesai',
            'closed'    => 'Closed',
            default     => ucfirst($this->status),
        };
    }

    /**
     * Accessor: Total harga terformat.
     */
    public function getTotalFormatAttribute(): string
    {
        return 'Rp ' . number_format($this->total_harga, 0, ',', '.');
    }

    /**
     * Scope: Filter pesanan berdasarkan status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
