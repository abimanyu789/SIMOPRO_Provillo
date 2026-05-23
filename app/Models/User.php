<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model User - Pengguna sistem SIMOPRO (Admin).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $jabatan
 * @property string|null $foto
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Field yang dapat diisi secara massal.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'jabatan',
        'foto',
    ];

    /**
     * Field yang disembunyikan dari serialisasi.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast tipe data field.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Accessor: URL foto profil pengguna.
     * Mengembalikan foto default jika tidak ada foto.
     */
    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(storage_path('app/public/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }
        return asset('images/default-avatar.png');
    }
}
