<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonitoringTargetPetugas extends Model
{
    protected $table = 'monitoring_target_petugas';
    protected $primaryKey = 'id_monitoring';
    protected $fillable = [
        'thblrek', 'tgl_monitoring', 'kddk', 'id_petugas', 'saldo_awal_rp', 'target_threshold_rp',
    ];

    public function petugas()
    {
        return $this->belongsTo(Petugas::class, 'id_petugas', 'id_petugas');
    }
}
