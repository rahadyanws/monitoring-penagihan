<?php

namespace App\Http\Controllers;

use App\Models\MappingKddkPetugas;
use App\Models\MonitoringTargetPetugas;
use App\Models\Pelanggan;
use App\Models\RefPosko;
use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardPoskoPetugasController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $thblrek = (int) $now->format('Ym');
        $poskoId = $request->input('posko_id');

        $poskos = RefPosko::orderBy('nama_posko')->get();

        $mappingQuery = MappingKddkPetugas::with(['petugas.posko']);
        if ($poskoId) {
            $mappingQuery->whereHas('petugas', fn ($q) => $q->where('id_posko', $poskoId));
        }
        $mappings = $mappingQuery->get();

        $petugasRows = [];
        $totalTarget = 0;
        $totalRealisasi = 0;
        $totalSaldoAwal = 0;

        foreach ($mappings as $mapping) {
            $target = MonitoringTargetPetugas::where('thblrek', $thblrek)
                ->where('kddk', $mapping->kddk)
                ->first();

            $idpelList = Pelanggan::where('kddk', $mapping->kddk)->pluck('idpel');
            $realisasi = (float) TransaksiPembayaranHarian::whereIn('idpel', $idpelList)
                ->where('thblrek', $thblrek)
                ->sum('rp_tagihan');

            $targetRp = (float) ($target->target_threshold_rp ?? 0);
            $saldoAwal = (float) ($target->saldo_awal_rp ?? 0);
            $gap = max($targetRp - $realisasi, 0);
            $gapPercent = $targetRp > 0 ? round(($gap / $targetRp) * 100, 1) : 0;

            $petugasRows[] = [
                'posko' => $mapping->petugas?->posko?->nama_posko ?? '-',
                'petugas' => $mapping->petugas?->nama_petugas ?? '-',
                'pbm' => $mapping->petugas?->nama_pbm ?? '-',
                'kddk' => $mapping->kddk,
                'saldo_awal_rp' => $saldoAwal,
                'realisasi_rp' => $realisasi,
                'target_threshold_rp' => $targetRp,
                'gap_rp' => $gap,
                'gap_percentage' => $gapPercent,
            ];

            $totalTarget += $targetRp;
            $totalRealisasi += $realisasi;
            $totalSaldoAwal += $saldoAwal;
        }

        // Grafik realisasi harian bulan berjalan vs garis target threshold tgl 20
        $targetLineHarian = $totalTarget > 0 ? round($totalTarget / 20) : 0;
        $chartHarian = [];
        $awalBulan = $now->copy()->startOfMonth();

        for ($d = 1; $d <= $now->day; $d++) {
            $tanggal = $awalBulan->copy()->addDays($d - 1);

            $idpelListSemua = $poskoId
                ? Pelanggan::whereIn('kddk', $mappings->pluck('kddk'))->pluck('idpel')
                : null;

            $q = TransaksiPembayaranHarian::whereDate('tgl_transaksi', $tanggal)->where('thblrek', $thblrek);
            if ($idpelListSemua) {
                $q->whereIn('idpel', $idpelListSemua);
            }

            $chartHarian[] = [
                'tgl' => $tanggal->format('Y-m-d'),
                'realisasi_rp' => (float) $q->sum('rp_tagihan'),
                'target_line_rp' => $targetLineHarian,
            ];
        }

        $summary = [
            'target_threshold_rp' => $totalTarget,
            'realisasi_bulanan_rp' => $totalRealisasi,
            'gap_threshold_rp' => max($totalTarget - $totalRealisasi, 0),
            'realisasi_harian_rp' => $chartHarian[count($chartHarian) - 1]['realisasi_rp'] ?? 0,
        ];

        return view('dashboard.posko-petugas', [
            'poskos' => $poskos,
            'poskoId' => $poskoId,
            'summary' => $summary,
            'chartHarian' => $chartHarian,
            'petugasRows' => collect($petugasRows)->sortByDesc('realisasi_rp')->values(),
            'periode' => $now->translatedFormat('F Y'),
        ]);
    }
}
