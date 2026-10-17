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
        $kondisi = strtolower($pengembalian->kondisi_kembali);

        // KUNCI PERBAIKAN: Format teks log disesuaikan dengan kondisi barang
        $rincianAlat = $peminjaman->detailPinjams->map(function ($d) use ($kondisi, $pengembalian) {
            $namaAlat = $d->alat->nama_alat ?? 'Alat';
            
            // Jika Hilang / Rusak Berat, catat di log bahwa stok TIDAK bertambah
            if (str_contains($kondisi, 'hilang') || str_contains($kondisi, 'berat')) {
                return "{$namaAlat} (stok tidak bertambah - {$pengembalian->kondisi_kembali})";
            }
            
            return "{$namaAlat} (+{$d->jumlah} stok)";
        })->join(', ');

        $infoDenda = $pengembalian->denda > 0 ? " Denda: Rp " . number_format($pengembalian->denda, 0, ',', '.') : " Bebas denda.";

        $this->catatLog("Memverifikasi pengembalian alat dari {$namaPeminjam} (ID Transaksi: #{$peminjaman->id}). Kondisi: '{$pengembalian->kondisi_kembali}'. Rincian: {$rincianAlat}.{$infoDenda}");
    }

    public function deleting(Pengembalian $pengembalian): void
    {
        $peminjaman = $pengembalian->peminjaman;

        if ($peminjaman) {
            $kondisi = strtolower($pengembalian->kondisi_kembali);

            // 1. Kurangi stok pada kolom kondisi yang sesuai saat pengembalian dibatalkan/direset
            foreach ($peminjaman->detailPinjams as $d) {
                if ($d->alat) {
                    $alat = $d->alat;

                    if (str_contains($kondisi, 'hilang')) {
                        // Kurangi dari stok_hilang
                        $alat->decrement('stok_hilang', min($d->jumlah, $alat->stok_hilang));
                    } elseif (str_contains($kondisi, 'berat')) {
                        // Kurangi dari stok_rusak_berat
                        $alat->decrement('stok_rusak_berat', min($d->jumlah, $alat->stok_rusak_berat));
                    } elseif (str_contains($kondisi, 'ringan')) {
                        // Kurangi dari stok_rusak_ringan
                        $alat->decrement('stok_rusak_ringan', min($d->jumlah, $alat->stok_rusak_ringan));
                    } else {
                        // Kurangi dari stok_baik
                        $alat->decrement('stok_baik', min($d->jumlah, $alat->stok_baik));
                    }
                }
            }

            // 2. Kembalikan status peminjaman menjadi 'dipinjam'
            $peminjaman->update(['status' => 'dipinjam']);

            $namaPeminjam = $peminjaman->user->name ?? 'User';
            $rincianAlat = $peminjaman->detailPinjams->map(function ($d) {
                $namaAlat = $d->alat->nama_alat ?? 'Alat';
                return "{$namaAlat} (-{$d->jumlah} unit)";
            })->join(', ');

            // 3. Catat Log Aktivitas
            $this->catatLog("Reset pengembalian alat milik {$namaPeminjam} (ID Transaksi: #{$peminjaman->id}). Status dikembalikan menjadi 'dipinjam' dan penyesuaian stok alat: {$rincianAlat}.");
        }
    }
}