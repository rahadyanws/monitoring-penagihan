<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaksi_pembayaran_harian', function (Blueprint $table) {
            $table->id('id_transaksi');
            $table->date('tgl_transaksi');
            $table->unsignedBigInteger('idpel');
            $table->string('tarif', 10);
            $table->integer('daya');
            $table->integer('thblrek');
            $table->decimal('rp_tagihan', 15, 2);
            $table->decimal('rp_bk', 15, 2)->default(0); // denda keterlambatan
            $table->string('kode_status', 10)->nullable();
            $table->timestamps();

            $table->foreign('idpel')->references('idpel')->on('pelanggan')->cascadeOnDelete();
            $table->index('tgl_transaksi');
            $table->index('thblrek');
            $table->index('idpel');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_pembayaran_harian');
    }
};
