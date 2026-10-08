<?php

namespace App\Observers;

use App\Models\Alat;
use App\Models\LogAktivitas;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class AlatObserver implements ShouldHandleEventsAfterCommit
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

    public function created(Alat $alat): void
    {
        $this->catatLog("Menambahkan master data alat baru: {$alat->nama_alat} (Stok Awal: {$alat->stok})");
    }

    public function updated(Alat $alat): void
    {
        $changes = $alat->getChanges();
        unset($changes['updated_at'], $changes['created_at']);

        if (empty($changes)) {
            return;
        }

        // Jika perubahan stok dipicu dari transaksi peminjaman/pengembalian, skip log di sini
        if (app()->has('skip_alat_log') && app('skip_alat_log') === true) {
            return;
        }

        $details = [];
        foreach ($changes as $field => $newValue) {
            $oldValue = $alat->getOriginal($field);
            
            // Format khusus jika yang diubah adalah stok secara manual oleh Admin
            if ($field === 'stok') {
                // HINDARI LOG JIKA ANGKANYA SAMA (Selisih 0)
                if ($oldValue == $newValue) {
                    continue;
                }

                $selisih = $newValue - $oldValue;
                $keterangan = $selisih > 0 ? "bertambah {$selisih}" : "berkurang " . abs($selisih);
                $details[] = "stok {$keterangan} ({$oldValue} -> {$newValue})";
            } else {
                $old = $oldValue ?? 'kosong';
                $new = $newValue ?? 'kosong';
                $details[] = "{$field}: '{$old}' -> '{$new}'";
            }
        }

        // Jika tidak ada perubahan berarti (misal hanya stok bernilai sama), hentikan
        if (empty($details)) {
            return;
        }

        $perubahan = implode(', ', $details);
        $this->catatLog("Memperbarui master alat '{$alat->nama_alat}' (ID: {$alat->id}) - {$perubahan}");
    }

    public function deleted(Alat $alat): void
    {
        $this->catatLog("Menghapus master data alat: {$alat->nama_alat} (ID: {$alat->id})");
    }
}