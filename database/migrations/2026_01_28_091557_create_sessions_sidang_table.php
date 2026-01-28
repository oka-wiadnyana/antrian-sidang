<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('antrian_sidang', function (Blueprint $table) {
            $table->id();

            // ============================================
            // DATA PERKARA (Copy dari SIPP)
            // ============================================
            $table->string('perkara_id')->unique(); // PK dari SIPP
            $table->string('nomor_perkara')->index();
            $table->integer('alur_perkara_id')->nullable();
            $table->string('jenis_perkara')->nullable();

            // ============================================
            // WAKTU SIDANG
            // ============================================
            $table->date('tanggal_sidang')->index();
            $table->dateTime('waktu_sidang_efektif')->nullable();

            // ============================================
            // AGENDA & JENIS
            // ============================================
            $table->string('agenda')->nullable();
            $table->string('jenis_sidang')->nullable(); // 'sidang_biasa', 'mediasi', 'pk'

            // ============================================
            // DATA HAKIM & PANITERA (Copy dari SIPP)
            // ============================================
            $table->string('hakim_ketua')->nullable();
            $table->string('panitera_active')->nullable();
            $table->string('mediator_text')->nullable();

            // ============================================
            // JUMLAH PIHAK (Calculated)
            // ============================================
            $table->integer('jumlah_pihak1')->default(0); // Penggugat/Pemohon
            $table->integer('jumlah_pihak2')->default(0); // Tergugat/Termohon
            $table->integer('jumlah_pihak3')->default(0); // Intervensi
            $table->integer('jumlah_pihak4')->default(0); // Turut Tergugat

            // ============================================
            // TEXT PIHAK (Untuk panggilan sidang)
            // ============================================
            $table->text('pihak1_text')->nullable();
            $table->text('pihak2_text')->nullable();

            // ============================================
            // STATUS KEHADIRAN (Calculated dari checkin_pihak)
            // ============================================
            $table->string('status_kehadiran_pihak')->nullable(); // "2/3"

            $table->timestamps();

            // ============================================
            // INDEXES untuk performa
            // ============================================
            $table->index(['tanggal_sidang', 'waktu_sidang_efektif']);
            $table->index(['tanggal_sidang', 'hakim_ketua']);
            $table->index(['tanggal_sidang', 'jenis_sidang']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('antrian_sidang');
    }
};
