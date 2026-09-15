<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_posko', function (Blueprint $table) {
            $table->id('id_posko');
            $table->string('id_ulp', 10);
            $table->string('nama_posko', 50); // KOTA 1, KOTA 2, KOTA 3, LOHBENER, ARAHAN
            $table->timestamps();

            $table->foreign('id_ulp')->references('id_ulp')->on('ref_ulp')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_posko');
    }
};
