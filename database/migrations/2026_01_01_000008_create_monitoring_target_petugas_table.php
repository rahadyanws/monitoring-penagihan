<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_target_petugas', function (Blueprint $table) {
            $table->id('id_monitoring');
            $table->integer('thblrek');
            $table->date('tgl_monitoring');
            $table->string('kddk', 20);
            $table->unsignedBigInteger('id_petugas');
            $table->decimal('saldo_awal_rp', 15, 2)->default(0);
            $table->decimal('target_threshold_rp', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_petugas')->references('id_petugas')->on('petugas')->cascadeOnDelete();
            $table->unique(['thblrek', 'tgl_monitoring', 'kddk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('monitoring_target_petugas');
    }
};
