<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas', function (Blueprint $table) {
            $table->id('id_petugas');
            $table->unsignedBigInteger('id_posko')->nullable();
            $table->string('nama_petugas', 100);
            $table->string('nama_pbm', 100);
            $table->string('no_telepon', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('id_posko')->references('id_posko')->on('ref_posko')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas');
    }
};
