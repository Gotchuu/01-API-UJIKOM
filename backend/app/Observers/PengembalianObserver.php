<?php

namespace App\Observers;

use App\Models\Pengembalian;
use App\Models\LogAktivitas;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class PengembalianObserver implements ShouldHandleEventsAfterCommit
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

    public function created(Pengembalian $pengembalian): void
    {
        $peminjaman = $pengembalian->peminjaman;
        $namaPeminjam = $peminjaman->user->name ?? 'User';
        
        $rincianAlat = $peminjaman->detailPinjams->map(function ($d) {
            $namaAlat = $d->alat->nama_alat ?? 'Alat';
            return "{$namaAlat} (+{$d->jumlah} stok)";
        })->join(', ');

        $infoDenda = $pengembalian->denda > 0 ? " Denda: Rp " . number_format($pengembalian->denda, 0, ',', '.') : " Bebas denda.";

        $this->catatLog("Memverifikasi pengembalian alat dari {$namaPeminjam} (ID Transaksi: #{$peminjaman->id}). Kondisi: '{$pengembalian->kondisi_kembali}'. Barang dikembalikan: {$rincianAlat}.{$infoDenda}");
    }

    /**
     * Dipanggil otomatis saat data pengembalian di-reset / dihapus.
     */
        public function deleted(Pengembalian $pengembalian): void
    {
        $peminjaman = $pengembalian->peminjaman;

        if ($peminjaman) {
            // 1. Validasi Stok: Cek apakah stok alat mencukupi untuk dipotong kembali
            foreach ($peminjaman->detailPinjams as $d) {
                if ($d->alat) {
                    // Jika stok alat saat ini lebih kecil dari Qty yang dulu dipinjam
                    if ($d->alat->stok < $d->jumlah) {
                        $namaAlat = $d->alat->nama_alat ?? 'Alat';
                        // Lempar Exception agar DB::rollBack() di Controller berjalan!
                        throw new \Exception("Gagal reset pengembalian: Stok '{$namaAlat}' saat ini tinggal {$d->alat->stok} unit (dibutuhkan {$d->jumlah} unit untuk di-reset).");
                    }
                }
            }

            // 2. Jika semua stok alat aman/cukup, lakukan pemotongan stok
            foreach ($peminjaman->detailPinjams as $d) {
                if ($d->alat) {
                    $d->alat->decrement('stok', $d->jumlah);
                }
            }

            // 3. Ubah status peminjaman kembali menjadi 'dipinjam'
            $peminjaman->update(['status' => 'dipinjam']);

            $namaPeminjam = $peminjaman->user->name ?? 'User';
            $rincianAlat = $peminjaman->detailPinjams->map(function ($d) {
                $namaAlat = $d->alat->nama_alat ?? 'Alat';
                return "{$namaAlat} (-{$d->jumlah} stok)";
            })->join(', ');

            // 4. Catat Log Aktivitas
            $this->catatLog("Reset pengembalian alat milik {$namaPeminjam} (ID Transaksi: #{$peminjaman->id}). Status dikembalikan menjadi 'dipinjam' dan penyesuaian stok alat: {$rincianAlat}.");
        }
    }
}