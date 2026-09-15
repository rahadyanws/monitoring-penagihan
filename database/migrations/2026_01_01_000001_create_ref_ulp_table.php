<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ref_ulp', function (Blueprint $table) {
            $table->string('id_ulp', 10)->primary();
            $table->string('nama_ulp', 100);
            $table->string('unit_ap', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ref_ulp');
    }
};
