<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pesan_perbaikan', function (Blueprint $table) {
            $table->dropForeign(['pengembalian_id']);
            $table->foreignId('pengembalian_id')->nullable()->change();
            $table->foreign('pengembalian_id')->references('id')->on('pengembalian')->nullOnDelete();
        });
    }
    public function down(): void {
        Schema::table('pesan_perbaikan', function (Blueprint $table) {
            $table->dropForeign(['pengembalian_id']);
            $table->foreignId('pengembalian_id')->nullable(false)->change();
            $table->foreign('pengembalian_id')->references('id')->on('pengembalian')->cascadeOnDelete();
        });
    }
};