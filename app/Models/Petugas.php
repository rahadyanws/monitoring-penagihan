<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Petugas extends Model
{
    protected $table = 'petugas';
    protected $primaryKey = 'id_petugas';
    protected $fillable = ['id_posko', 'nama_petugas', 'nama_pbm', 'no_telepon', 'is_active'];

    public function posko()
    {
        return $this->belongsTo(RefPosko::class, 'id_posko', 'id_posko');
    }

    public function kddkList()
    {
        return $this->hasMany(MappingKddkPetugas::class, 'id_petugas', 'id_petugas');
    }
}
