<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mapping_kddk_petugas', function (Blueprint $table) {
            $table->string('kddk', 20)->primary();
            $table->unsignedBigInteger('id_petugas')->nullable();
            $table->string('keterangan', 100)->nullable();
            $table->timestamp('updated_at')->useCurrent();

            $table->foreign('id_petugas')->references('id_petugas')->on('petugas')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mapping_kddk_petugas');
    }
};
