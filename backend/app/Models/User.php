<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Laravel\Sanctum\HasApiTokens; // digunakan untuk mengimpor trait otentikasi API yang disediakan oleh Laravel Sanctum

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // berfungsi untuk mengimpor kelas relasi satu-ke-banyak (One-to-Many) bawaan Laravel


class User extends Authenticatable
{
    
    use HasApiTokens, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'role', 'no_hp', 'alamat',
        'foto_profile',

    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function peminjaman(): HasMany{
        return $this->hasMany(Peminjaman::class);
    }

    public function LogAktivitas(): HasMany{
        return $this->hasMany(LogAktivitas::class);
    }

    public function scopeTersedia($query){
        return $query->where('stok', '>', 0)->where('status_kondisi', 'Baik');
    }

}
