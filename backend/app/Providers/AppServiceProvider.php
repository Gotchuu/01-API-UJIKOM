<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Import Model
use App\Models\Pengembalian;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Kategori;
use App\Models\User;

// Import Observer
use App\Observers\PengembalianObserver;
use App\Observers\AlatObserver;
use App\Observers\PeminjamanObserver;
use App\Observers\KategoriObserver;
use App\Observers\UserObserver;


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