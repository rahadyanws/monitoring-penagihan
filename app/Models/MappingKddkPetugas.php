<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MappingKddkPetugas extends Model
{
    protected $table = 'mapping_kddk_petugas';
    protected $primaryKey = 'kddk';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    protected $fillable = ['kddk', 'id_petugas', 'keterangan'];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }
}
