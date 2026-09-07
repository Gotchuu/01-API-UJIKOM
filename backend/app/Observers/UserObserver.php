<?php
namespace App\Observers;
use App\Models\User;
use App\Models\LogAktivitas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class UserObserver implements ShouldHandleEventsAfterCommit
{
    private function catatLog(string $pesan): void
    {
        $userId = Auth::id() ?? auth()->id();
        if ($userId) {
            LogAktivitas::create(['user_id' => $userId, 'aktivitas' => $pesan]);
        }
    }
    public function created(User $user): void
    {
        $this->catatLog("Menambahkan user baru: {$user->name} ({$user->role}) (ID: {$user->id})");
    }
    public function updated(User $user): void
    {
        if (!$user->wasChanged(['name','email','role'])) return;
        $changes = [];
        foreach (['name','email','role'] as $field) {
            if ($user->wasChanged($field)) {
                $changes[] = "$field: '{$user->getOriginal($field)}' -> '{$user->$field}'";
            }
        }
        $this->catatLog("Memperbarui user ID: {$user->id} - " . implode(', ', $changes));
    }
    public function deleted(User $user): void
    {
        $this->catatLog("Menghapus user: {$user->name} ({$user->role}) (ID: {$user->id})");
    }
}