<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\TagihanRekeningBulanan;
use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardDabesController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $thblrek = (int) $now->format('Ym');
        $statusFilter = $request->input('status_lunas'); // '1' | '0' | null

        $pelangganDabes = Pelanggan::query()
            ->whereHas('tagihan', fn ($q) => $q->where('daya', '>=', Pelanggan::AMBANG_DABES_VA)->where('thblrek', $thblrek))
            ->with(['tagihan' => fn ($q) => $q->where('thblrek', $thblrek)])
            ->get();

        $items = [];
        $totalBelumLunas = 0;
        $nominalBelumLunas = 0;

        foreach ($pelangganDabes as $p) {
            $tagihanIni = $p->tagihan->first();
            if (! $tagihanIni) {
                continue;
            }

            $riwayat = TransaksiPembayaranHarian::where('idpel', $p->idpel)
                ->orderByDesc('tgl_transaksi')
                ->limit(12)
                ->get();

            $avgHari = $riwayat->isNotEmpty()
                ? (int) round($riwayat->avg(fn ($t) => $t->tgl_transaksi->day))
                : null;

            $statusLunas = (bool) $tagihanIni->status_lunas;
            $isAlert = ! $statusLunas && $avgHari !== null && $now->day > $avgHari;

            if ($statusFilter !== null && $statusFilter !== '' && ((int) $statusFilter === 1) !== $statusLunas) {
                continue;
            }

            if (! $statusLunas) {
                $totalBelumLunas++;
                $nominalBelumLunas += (float) $tagihanIni->rp_tag;
            }

            $items[] = [
                'idpel' => $p->idpel,
                'nama' => $p->nama,
                'tarif' => $tagihanIni->tarif,
                'daya' => $tagihanIni->daya,
                'rp_tag' => (float) $tagihanIni->rp_tag,
                'avg_tgl_bayar' => $avgHari,
                'status_lunas' => $statusLunas,
                'is_alert_overdue' => $isAlert,
                'riwayat' => $riwayat->sortBy('tgl_transaksi')->values()->map(fn ($t) => [
                    'thblrek' => $t->thblrek,
                    'tgl_transaksi' => $t->tgl_transaksi->format('Y-m-d'),
                    'hari' => $t->tgl_transaksi->day,
                ]),
            ];
        }

        $items = collect($items)->sortByDesc('is_alert_overdue')->values();

        return view('dashboard.dabes', [
            'items' => $items,
            'totalDabes' => $pelangganDabes->count(),
            'totalBelumLunas' => $totalBelumLunas,
            'nominalBelumLunas' => $nominalBelumLunas,
            'jumlahAlert' => $items->where('is_alert_overdue', true)->count(),
            'periode' => $now->translatedFormat('F Y'),
            'statusFilter' => $statusFilter,
        ]);
    }
}
