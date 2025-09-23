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
        Schema::create('checkin_pihak', function (Blueprint $table) {
            $table->id();
            $table->integer('perkara_id');
            $table->enum('tipe_pihak', ['pihak1', 'pihak2']);
            $table->string('nama_yang_hadir');
            $table->enum('status_kehadiran', ['pihak_langsung', 'kuasa'])->default('pihak_langsung');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->double('jarak_meter')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('waktu_checkin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkin_pihak');
    }
};
