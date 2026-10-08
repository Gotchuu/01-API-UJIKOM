<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany; // berfungsi untuk mengimpor kelas relasi satu-ke-banyak (One-to-Many) bawaan Laravel
use Illuminate\Database\Eloquent\SoftDeletes;

class Kategori extends Model
{
    use SoftDeletes;

    protected $table = 'kategori';

    protected $fillable = ['nama_kategori'];

    public function alat(): HasMany
    {
        return $this->hasMany(Alat::class);
    }
}
