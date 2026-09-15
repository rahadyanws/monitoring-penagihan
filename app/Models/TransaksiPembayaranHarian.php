<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiPembayaranHarian extends Model
{
    protected $table = 'transaksi_pembayaran_harian';
    protected $primaryKey = 'id_transaksi';
    protected $fillable = [
        'tgl_transaksi', 'idpel', 'tarif', 'daya', 'thblrek', 'rp_tagihan', 'rp_bk', 'kode_status',
    ];
    protected $casts = [
        'tgl_transaksi' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'idpel', 'idpel');
    }
}
