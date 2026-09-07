<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // berfungsi untuk mengimpor kelas relasi satu-ke-banyak (One-to-Many) bawaan Laravel

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $fillable = ['nama_kategori'];

    public function alat(): HasMany {
        return $this->hasMany(Alat::class);
    }
}
