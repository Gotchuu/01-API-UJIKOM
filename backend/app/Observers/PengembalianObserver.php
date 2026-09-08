<?php

namespace App\Observers;

use App\Models\LogAktivitas;
use App\Models\Pengembalian;
use Carbon\Carbon;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PengembalianObserver implements ShouldHandleEventsAfterCommit
{
    /**
     * Otomatis tergolong saat Pengembalian baru dibuat via API
     */
    public function created(Pengembalian $pengembalian): void
    {
        LogAktivitas::create([
            'user_id' => auth()->id() ?? $pengembalian->petugas_id,
            'aktivitas' => "Memproses pengembalian peminjaman ID: #{$pengembalian->peminjaman_id} dengan status kondisi: {$pengembalian->kondisi_kembali}.",
        ]);
    }

    /**
     * Hanya log kalau ada perubahan penting (kondisi/denda/tgl_kembali)
     */
    public function updated(Pengembalian $pengembalian): void
    {
        $changes = $pengembalian->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return;
        }

        if (! $pengembalian->wasChanged(['kondisi_kembali', 'denda', 'tgl_kembali'])) {
            return;
        }

        $details = [];
        if ($pengembalian->wasChanged('kondisi_kembali')) {
            $old = $pengembalian->getOriginal('kondisi_kembali');
            $new = $pengembalian->kondisi_kembali;
            $details[] = "kondisi: '{$old}' -> '{$new}'";
        }
        if ($pengembalian->wasChanged('denda')) {
            $old = $pengembalian->getOriginal('denda');
            $new = $pengembalian->denda;
            $details[] = "denda: Rp{$old} -> Rp{$new}";
        }
        if ($pengembalian->wasChanged('tgl_kembali')) {
            $old = $pengembalian->getOriginal('tgl_kembali');
            $new = $pengembalian->tgl_kembali instanceof Carbon ? $pengembalian->tgl_kembali->format('Y-m-d') : $pengembalian->tgl_kembali;
            $details[] = "tgl_kembali: '{$old}' -> '{$new}'";
        }

        $perubahan = implode(', ', $details);

        LogAktivitas::create([
            'user_id' => auth()->id() ?? $pengembalian->petugas_id,
            'aktivitas' => "Mengubah data pengembalian ID: #{$pengembalian->id} (Peminjaman #{$pengembalian->peminjaman_id}) - {$perubahan}",
        ]);
    }
}
