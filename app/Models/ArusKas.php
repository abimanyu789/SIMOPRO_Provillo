<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Model ArusKas - Manajemen Arus Kas (Cash Flow).
 *
 * Mendukung polymorphic reference ke sumber transaksi
 * (pesanan, produksi, dll) melalui referensi_id dan referensi_type.
 *
 * @property string $jenis pemasukan|pengeluaran
 */
class ArusKas extends Model
{
    use SoftDeletes;

    protected $table = 'arus_kas';

    protected $fillable = [
        'kode_transaksi',
        'jenis',
        'kategori',
        'deskripsi',
        'jumlah',
        'tanggal',
        'referensi_id',
        'referensi_type',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
    ];

    /**
     * Polymorphic relasi ke sumber transaksi.
     */
    public function referensi(): MorphTo
    {
        return $this->morphTo('referensi');
    }

    /**
     * Accessor: Jumlah terformat.
     */
    public function getJumlahFormatAttribute(): string
    {
        $prefix = $this->jenis === 'pemasukan' ? '+' : '-';
        return $prefix . ' Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Accessor: Warna berdasarkan jenis transaksi.
     */
    public function getJenisColorAttribute(): string
    {
        return $this->jenis === 'pemasukan' ? 'text-success' : 'text-danger';
    }

    /**
     * Scope: Filter berdasarkan jenis transaksi.
     */
    public function scopePemasukan($query)
    {
        return $query->where('jenis', 'pemasukan');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('jenis', 'pengeluaran');
    }
}
