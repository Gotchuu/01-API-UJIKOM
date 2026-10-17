<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alat extends Model
{
    use SoftDeletes;
    
    protected $table = 'alat';

    protected $fillable = [
        'kategori_id',
        'nama_alat',
        'stok',
        'stok_baik',
        'stok_rusak_ringan',
        'stok_rusak_berat',
        'stok_hilang',
        'status_kondisi',
        'deskripsi',
        'gambar',
    ];

    protected function casts(): array
    {
        return [
            'stok' => 'integer',
            'stok_baik' => 'integer',
            'stok_rusak_ringan' => 'integer',
            'stok_rusak_berat' => 'integer',
            'stok_hilang' => 'integer',
        ];
    }

    /**
     * Accessor Stok Otomatis:
     * Mengembalikan stok_baik jika stok dipanggil langsung ($alat->stok)
     */
    public function getStokAttribute($value)
    {
        return $this->attributes['stok_baik'] ?? $value ?? 0;
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }

    public function detailPinjam(): HasMany
    {
        return $this->hasMany(DetailPinjam::class);
    }
}