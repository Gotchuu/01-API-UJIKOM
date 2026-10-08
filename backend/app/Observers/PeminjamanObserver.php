<?php

namespace App\Observers;

use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class PeminjamanObserver implements ShouldHandleEventsAfterCommit
{
    private function catatLog(string $pesan): void
    {
        $userId = Auth::id() ?? auth()->id();
        if ($userId) {
            LogAktivitas::create([
                'user_id' => $userId,
                'aktivitas' => $pesan,
            ]);
        }
    }

    public function updated(Peminjaman $peminjaman): void
    {
        // Pastikan relasi terisi
        $peminjaman->loadMissing('user', 'detailPinjams.alat');

        $statusLama = $peminjaman->getOriginal('status');
        $statusBaru = $peminjaman->status;

        $namaPeminjam = $peminjaman->user->name ?? 'User';

        $rincianAlat = $peminjaman->detailPinjams->map(function ($d) {
            $namaAlat = $d->alat->nama_alat ?? 'Alat';
            return "{$namaAlat} ({$d->jumlah} unit)";
        })->join(', ');

        // Log saat Petugas menyetujui peminjaman (status berubah jadi 'dipinjam')
        if ($statusBaru === 'dipinjam') {
            $this->catatLog("Menyetujui peminjaman ID #{$peminjaman->id} untuk {$namaPeminjam}. Barang dipinjam: {$rincianAlat}");
        } 
        // Log saat Peminjam mengajukan pengembalian
        elseif ($statusBaru === 'diproses') {
            $this->catatLog("Peminjam {$namaPeminjam} mengajukan pengembalian alat untuk transaksi ID #{$peminjaman->id}");
        }
    }
}