<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefUlp extends Model
{
    protected $table = 'ref_ulp';
    protected $primaryKey = 'id_ulp';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id_ulp', 'nama_ulp', 'unit_ap'];

    public function poskos()
    {
        return $this->hasMany(RefPosko::class, 'id_ulp', 'id_ulp');
    }
}
