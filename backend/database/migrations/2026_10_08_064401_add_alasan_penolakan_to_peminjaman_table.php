<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            // 1. Ubah enum status dengan menambahkan 'ditolak'
            $table->enum('status', ['diajukan', 'dipinjam', 'diproses', 'dikembalikan', 'telat', 'ditolak'])
                  ->default('diajukan')
                  ->change();

            // 2. Tambahkan kolom alasan_penolakan
            $table->text('alasan_penolakan')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->enum('status', ['diajukan', 'dipinjam', 'diproses', 'dikembalikan', 'telat'])
                  ->default('diajukan')
                  ->change();

            $table->dropColumn('alasan_penolakan');
        });
    }
};