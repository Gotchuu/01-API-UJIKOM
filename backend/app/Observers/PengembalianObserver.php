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
     * Menggunakan "deleting" agar jika stok 0 / tidak mencukupi, 
     * Exception dilempar SEBELUM baris data pengembalian benar-benar terhapus dari DB.
     */
    public function deleting(Pengembalian $pengembalian): void
    {
        $peminjaman = $pengembalian->peminjaman;

        if ($peminjaman) {
            // 1. Validasi Ketat Stok Alat
            foreach ($peminjaman->detailPinjams as $d) {
                if ($d->alat) {
                    $namaAlat = $d->alat->nama_alat ?? 'Alat';

                    // PROTEKSI 1: Tolak jika stok alat di gudang saat ini bernilai 0
                    if ($d->alat->stok <= 0) {
                        throw new \Exception("Gagal reset pengembalian: Stok '{$namaAlat}' saat ini 0 unit.");
                    }

                    // PROTEKSI 2: Tolak jika stok alat kurang dari Qty yang harus dipotong
                    if ($d->alat->stok < $d->jumlah) {
                        throw new \Exception("Gagal reset pengembalian: Stok '{$namaAlat}' saat ini tinggal {$d->alat->stok} unit (dibutuhkan {$d->jumlah} unit untuk di-reset).");
                    }
                }
            }

            // 2. Jika seluruh stok aman, kurangi stok alat
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