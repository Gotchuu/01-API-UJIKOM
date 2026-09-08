<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PesanPerbaikan extends Model {
    protected $table = 'pesan_perbaikan';
    protected $fillable = [
        'pengembalian_id','petugas_id','jenis','pesan','status','admin_id','admin_catatan'
        ];
    public function admin(): BelongsTo 
    { return $this->belongsTo(User::class, 'admin_id'); 
        }

    public function pengembalian(): BelongsTo { 
        return $this->belongsTo(Pengembalian::class); 
        }

    public function petugas(): BelongsTo { 
        return $this->belongsTo(User::class, 'petugas_id'); 
        }
        
}