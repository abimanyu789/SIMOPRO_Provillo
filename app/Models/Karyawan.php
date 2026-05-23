<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Karyawan - Data Master Karyawan Provillo.
 *
 * @property int $id
 * @property string $kode_karyawan
 * @property string $nama
 * @property string $posisi
 * @property string $status aktif|nonaktif
 */
class Karyawan extends Model
{
    use SoftDeletes;

    protected $table = 'karyawan';

    protected $fillable = [
        'kode_karyawan',
        'nama',
        'tanggal_lahir',
        'posisi',
        'divisi',
        'no_hp',
        'email',
        'alamat',
        'tanggal_bergabung',
        'no_rekening',
        'foto',
        'status',
        'deskripsi',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
    ];

    /**
     * Relasi ke produksi (one-to-many).
     */
    public function produksi(): HasMany
    {
        return $this->hasMany(Produksi::class, 'karyawan_id');
    }

    /**
     * Accessor: URL foto karyawan.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(storage_path('app/public/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }
        return asset('images/default-avatar.png');
    }

    /**
     * Accessor: Badge status karyawan.
     */
    public function getStatusBadgeAttribute(): string
    {
        return $this->status === 'aktif'
            ? '<span class="badge-success">Aktif</span>'
            : '<span class="badge-danger">Non-Aktif</span>';
    }
}
