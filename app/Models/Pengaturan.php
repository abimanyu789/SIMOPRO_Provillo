<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Pengaturan - Konfigurasi Aplikasi SIMOPRO.
 * Singleton pattern: hanya ada satu record konfigurasi.
 */
class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'nama_usaha',
        'deskripsi_usaha',
        'alamat',
        'no_hp',
        'email',
        'logo',
    ];

    /**
     * Mendapatkan konfigurasi aplikasi (singleton).
     * Jika belum ada, buat record default.
     */
    public static function getSetting(): self
    {
        return self::firstOrCreate([], [
            'nama_usaha' => 'Provillo',
            'deskripsi_usaha' => 'Produsen Sepatu Berkualitas',
        ]);
    }

    /**
     * Accessor: URL logo aplikasi.
     */
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo && file_exists(storage_path('app/public/' . $this->logo))) {
            return asset('storage/' . $this->logo);
        }
        return asset('images/provillo-logo.png');
    }
}
