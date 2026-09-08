<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('pesan_perbaikan', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete()->after('petugas_id');
            $table->text('admin_catatan')->nullable()->after('status');
        });
    }
    public function down(): void {
        Schema::table('pesan_perbaikan', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn(['admin_id','admin_catatan']);
        });
    }
};