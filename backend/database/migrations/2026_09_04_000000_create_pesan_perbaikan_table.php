<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
    public function up(): void {
        Schema::create('pesan_perbaikan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengembalian_id')->constrained('pengembalian')->cascadeOnDelete();
            $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
            $table->enum('jenis', ['kondisi','denda','tanggal','lainnya']);
            $table->text('pesan');
            $table->enum('status', ['terkirim','dibaca','selesai'])->default('terkirim');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('pesan_perbaikan'); }
};