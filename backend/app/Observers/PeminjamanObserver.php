<?php

namespace App\Observers;

use App\Models\Peminjaman;
use App\Models\LogAktivitas;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class PeminjamanObserver implements ShouldHandleEventsAfterCommit
{
    // Catat saat peminjam membuat pengajuan pinjaman
    public function created(Peminjaman $peminjaman): void
    {
        LogAktivitas::create([
            'user_id'   => auth()->id() ?? $peminjaman->user_id,
            'aktivitas' => "Mengajukan peminjaman alat baru (ID: #{$peminjaman->id})."
        ]);
    }

    // Catat saat status disetujui/ditolak oleh petugas/admin
    public function updated(Peminjaman $peminjaman): void
    {
        // wasChanged() harus dipakai di event `updated` (isDirty hanya work di `updating`)
        if ($peminjaman->wasChanged('status')) {
            $statusLama = $peminjaman->getOriginal('status');
            $statusBaru = $peminjaman->status;

            LogAktivitas::create([
                'user_id'   => auth()->id() ?? $peminjaman->user_id,
                'aktivitas' => "Mengubah status peminjaman ID: #{$peminjaman->id} dari '{$statusLama}' menjadi '{$statusBaru}'.",
            ]);
        }
    }
}
