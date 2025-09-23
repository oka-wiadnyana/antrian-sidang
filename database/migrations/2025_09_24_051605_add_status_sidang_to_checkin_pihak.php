<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::connection('mysql')->table('checkin_pihak', function (Blueprint $table) {
            $table->enum('status_sidang', ['belum_mulai', 'sedang_berlangsung', 'selesai'])
                ->default('belum_mulai')
                ->after('status_kehadiran');
        });
    }

    public function down()
    {
        Schema::connection('mysql')->table('checkin_pihak', function (Blueprint $table) {
            $table->dropColumn('status_sidang');
        });
    }
};
