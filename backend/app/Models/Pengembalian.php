<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes; // 1. Import Trait

class Pengembalian extends Model
{
    use SoftDeletes; // 2. Pakai Trait SoftDeletes

    protected $table = 'pengembalian';

    protected $fillable = [
        'peminjaman_id',
        'tgl_kembali',
        'kondisi_kembali',
        'denda',
        'petugas_id',
    ];

    protected function casts(): array
    {
        return [
            'tgl_kembali' => 'date:Y-m-d',
            'denda' => 'integer',
        ];
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}