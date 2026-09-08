<?php

namespace App\Providers;

use App\Models\Alat;
// Import Model
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use App\Observers\AlatObserver;
// Import Observer
use App\Observers\KategoriObserver;
use App\Observers\PeminjamanObserver;
use App\Observers\PengembalianObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Daftarkan seluruh Observer di sini
        Pengembalian::observe(PengembalianObserver::class);
        Alat::observe(AlatObserver::class);
        Peminjaman::observe(PeminjamanObserver::class);
        Kategori::observe(KategoriObserver::class); // BARU
        User::observe(UserObserver::class);
    }
}
