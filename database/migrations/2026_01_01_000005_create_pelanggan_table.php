<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->unsignedBigInteger('idpel')->primary(); // 12 digit IDPEL
            $table->string('nama', 150);
            $table->string('alamat', 255)->nullable();
            $table->string('kddk', 30);
            $table->string('unit_ap', 10);
            $table->string('unit_up', 10);
            // kogol: 0=Rumah Tangga, 1=Sosial, 2=Bisnis, 3=Industri, 9=Khusus
            // (PERLU KONFIRMASI CLIENT - lihat catatan open item di DATABASE_SCHEMA.md)
            $table->smallInteger('kogol');
            $table->timestamps();

            $table->index('kddk');
            $table->index('kogol');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};
