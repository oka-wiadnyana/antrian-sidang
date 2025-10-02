<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pastikan Anda mengimpor DB di sini jika Anda menggunakan DB::statement di bawah
        // use Illuminate\Support\Facades\DB; 

        Schema::table('checkin_pihak', function (Blueprint $table) {
            // Definisikan ulang kolom 'tipe_pihak' dengan semua opsi yang baru
            $table->enum('tipe_pihak', [
                'pihak1',
                'pihak2',
                'pihak3',
                'pihak4' // <--- Opsi BARU
            ])->change(); // <-- PENTING: Panggil change()
        });
    }

    public function down(): void
    {
        // Untuk rollback (kembali ke kondisi semula), hilangkan opsi yang baru
        Schema::table('checkin_pihak', function (Blueprint $table) {
            $table->enum('tipe_pihak', [
                'pihak1',
                'pihak2'
            ])->change();
        });
    }
};
