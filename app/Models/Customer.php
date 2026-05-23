<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Customer - Data Master Pelanggan Provillo.
 */
class Customer extends Model
{
    use SoftDeletes;

    protected $table = 'customers';

    protected $fillable = [
        'kode_customer',
        'nama',
        'no_hp',
        'email',
        'alamat',
        'kota',
        'provinsi',
        'kode_pos',
        'deskripsi',
    ];

    /**
     * Relasi ke pesanan (one-to-many).
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'customer_id');
    }
}
