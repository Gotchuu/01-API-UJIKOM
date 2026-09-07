<?php

namespace App\Observers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class AlatObserver implements ShouldHandleEventsAfterCommit
{
    private function catatLog(string $pesan): void
    {
        // Fallback: jika tidak ada auth (seeder/job), tetap coba catat dengan ID yang ada
        $userId = Auth::id() ?? auth()->id();
        if ($userId) {
            LogAktivitas::create([
                'user_id' => $userId,
                'aktivitas' => $pesan,
            ]);
        }
    }

    public function created(Alat $alat): void
    {
        $this->catatLog("Menambahkan master data alat baru: {$alat->nama_alat} (ID: {$alat->id})");
    }

        public function updated(Alat $alat): void
    {
        $changes = $alat->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return;
        }

        // JIKA cuma stok yang berubah dan dipicu dari transaksi peminjaman/pengembalian -> JANGAN log di sini
        // (sudah terwakili oleh log Peminjaman/Pengembalian biar tidak duplikat)
        // Cek via flag yang kita set di Controller (Langkah 2)
        if (app()->has('skip_alat_log') && app('skip_alat_log') === true) {
            return;
        }
        // Atau jika hanya stok yang berubah tanpa field lain, anggap sistem -> skip
        // Hapus baris di bawah ini jika kamu tetap ingin log stok manual
        if (count($changes) === 1 && isset($changes['stok'])) {
            return; // stok sistem tidak perlu log terpisah
        }

        $details = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $alat->getOriginal($field);
            $old = $oldValue ?? 'kosong';
            $new = $newValue ?? 'kosong';
            if (is_string($old) && strlen($old) > 50) $old = substr($old, 0, 50) . '...';
            if (is_string($new) && strlen($new) > 50) $new = substr($new, 0, 50) . '...';
            $details[] = "{$field}: '{$old}' -> '{$new}'";
        }

        $perubahan = implode(', ', $details);
        $this->catatLog("Memperbarui data alat '{$alat->nama_alat}' (ID: {$alat->id}) - {$perubahan}");
    }

    public function deleted(Alat $alat): void
    {
        $this->catatLog("Menghapus master data alat: {$alat->nama_alat} (ID: {$alat->id})");
    }
}
