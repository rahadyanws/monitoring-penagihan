<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'idpel';
    public $incrementing = false;
    protected $keyType = 'int';
    protected $fillable = ['idpel', 'nama', 'alamat', 'kddk', 'unit_ap', 'unit_up', 'kogol'];

    // Ambang batas Dabes (Daya Besar) disepakati > 53.000 VA (53 kVA)
    const AMBANG_DABES_VA = 53000;

    public function tagihan()
    {
        return $this->hasMany(TagihanRekeningBulanan::class, 'idpel', 'idpel');
    }

    public function transaksi()
    {
        return $this->hasMany(TransaksiPembayaranHarian::class, 'idpel', 'idpel');
    }
}
