<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefPosko extends Model
{
    protected $table = 'ref_posko';
    protected $primaryKey = 'id_posko';
    protected $fillable = ['id_ulp', 'nama_posko'];

    public function ulp()
    {
        return $this->belongsTo(RefUlp::class, 'id_ulp', 'id_ulp');
    }

    public function petugas()
    {
        return $this->hasMany(Petugas::class, 'id_posko', 'id_posko');
    }
}
