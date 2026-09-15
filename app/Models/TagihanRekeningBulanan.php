<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TagihanRekeningBulanan extends Model
{
    protected $table = 'tagihan_rekening_bulanan';
    protected $primaryKey = 'id_tagihan';
    protected $fillable = [
        'thblrek', 'idpel', 'tarif', 'daya', 'rp_tag', 'status_lunas', 'tgl_lunas',
    ];
    protected $casts = [
        'status_lunas' => 'boolean',
        'tgl_lunas' => 'date',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'idpel', 'idpel');
    }
}
