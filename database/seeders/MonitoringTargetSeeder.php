<?php

namespace Database\Seeders;

use App\Models\MappingKddkPetugas;
use App\Models\MonitoringTargetPetugas;
use App\Models\Pelanggan;
use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonitoringTargetSeeder extends Seeder
{
    // Target & saldo awal persis dari contoh tabel Dashboard Posko & Petugas di PRD.md
    protected array $targetTetap = [
        'ACA' => ['saldo_awal_rp' => 112711395, 'target_threshold_rp' => 84162076],
        'ACB' => ['saldo_awal_rp' => 92381966, 'target_threshold_rp' => 60312455],
        'ACD' => ['saldo_awal_rp' => 129848340, 'target_threshold_rp' => 94971948],
    ];

    public function run(): void
    {
        $now = Carbon::now();
        $thisMonth = (int) $now->format('Ym');

        foreach (MappingKddkPetugas::all() as $mapping) {
            if (isset($this->targetTetap[$mapping->kddk])) {
                $data = $this->targetTetap[$mapping->kddk];
            } else {
                // Estimasi target dari realisasi berjalan supaya gap % terlihat wajar (~25-40%)
                $realisasi = $this->realisasiBerjalan($mapping->kddk, $thisMonth);
                $rasio = random_int(60, 75) / 100; // realisasi saat ini = 60-75% dari target
                $target = $realisasi > 0 ? round($realisasi / $rasio) : random_int(30_000_000, 90_000_000);
                $data = [
                    'saldo_awal_rp' => round($target * (random_int(120, 160) / 100)),
                    'target_threshold_rp' => $target,
                ];
            }

            MonitoringTargetPetugas::create([
                'thblrek' => $thisMonth,
                'tgl_monitoring' => $now->toDateString(),
                'kddk' => $mapping->kddk,
                'id_petugas' => $mapping->id_petugas,
                'saldo_awal_rp' => $data['saldo_awal_rp'],
                'target_threshold_rp' => $data['target_threshold_rp'],
            ]);
        }
    }

    protected function realisasiBerjalan(string $kddk, int $thblrek): float
    {
        $idpelList = Pelanggan::where('kddk', $kddk)->pluck('idpel');

        return (float) TransaksiPembayaranHarian::whereIn('idpel', $idpelList)
            ->where('thblrek', $thblrek)
            ->sum('rp_tagihan');
    }
}
