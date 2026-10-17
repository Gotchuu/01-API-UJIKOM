<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            // Menambahkan kolom rincian stok setelah kolom stok utama
            $table->integer('stok_baik')->default(0)->after('stok');
            $table->integer('stok_rusak_ringan')->default(0)->after('stok_baik');
            $table->integer('stok_rusak_berat')->default(0)->after('stok_rusak_ringan');
            $table->integer('stok_hilang')->default(0)->after('stok_rusak_berat');
        });

        // OTOMATISASI DATA LAMA:
        // Pindahkan nilai pada kolom 'stok' lama ke kolom 'stok_baik' agar data yang sudah ada tidak hilang/nol
        DB::statement('UPDATE alat SET stok_baik = stok');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn([
                'stok_baik',
                'stok_rusak_ringan',
                'stok_rusak_berat',
                'stok_hilang'
            ]);
        });
    }
};