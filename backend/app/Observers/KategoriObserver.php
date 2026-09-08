<?php

namespace App\Observers;

use App\Models\Kategori;
use App\Models\LogAktivitas;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\Auth;

class KategoriObserver implements ShouldHandleEventsAfterCommit
{
    private function catatLog(string $pesan): void
    {
        $userId = Auth::id() ?? auth()->id();
        if ($userId) {
            LogAktivitas::create(['user_id' => $userId, 'aktivitas' => $pesan]);
        }
    }

    public function created(Kategori $kategori): void
    {
        $this->catatLog("Menambahkan kategori baru: {$kategori->nama_kategori} (ID: {$kategori->id})");
    }

    public function updated(Kategori $kategori): void
    {
        if (! $kategori->wasChanged('nama_kategori')) {
            return;
        }
        $old = $kategori->getOriginal('nama_kategori');
        $new = $kategori->nama_kategori;
        $this->catatLog("Mengubah kategori ID: {$kategori->id} dari '{$old}' menjadi '{$new}'");
    }

    public function deleted(Kategori $kategori): void
    {
        $this->catatLog("Menghapus kategori: {$kategori->nama_kategori} (ID: {$kategori->id})");
    }
}
