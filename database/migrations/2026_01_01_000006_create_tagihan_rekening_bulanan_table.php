<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_rekening_bulanan', function (Blueprint $table) {
            $table->id('id_tagihan');
            $table->integer('thblrek'); // YYYYMM
            $table->unsignedBigInteger('idpel');
            $table->string('tarif', 10);
            $table->integer('daya'); // VA
            $table->decimal('rp_tag', 15, 2); // pokok tagihan
            $table->boolean('status_lunas')->default(false);
            $table->date('tgl_lunas')->nullable();
            $table->timestamps();

            $table->foreign('idpel')->references('idpel')->on('pelanggan')->cascadeOnDelete();
            $table->unique(['thblrek', 'idpel']);
            $table->index(['thblrek', 'status_lunas']);
            $table->index('daya');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_rekening_bulanan');
    }
};
