<?php

namespace App\Http\Controllers;

use App\Models\MappingKddkPetugas;
use App\Models\Pelanggan;
use App\Models\TagihanRekeningBulanan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardLembarController extends Controller
{
    protected function bulanRelevan(): array
    {
        $now = Carbon::now();

        return [
            (int) $now->format('Ym'),
            (int) $now->copy()->subMonthNoOverflow()->format('Ym'),
            (int) $now->copy()->subMonthsNoOverflow(2)->format('Ym'),
        ];
    }

    public function index(Request $request)
    {
        $bulan = $this->bulanRelevan();

        $rows = [];
        $summary = [1 => ['plg' => 0, 'rp' => 0], 2 => ['plg' => 0, 'rp' => 0], 3 => ['plg' => 0, 'rp' => 0]];

        foreach (MappingKddkPetugas::with('petugas.posko')->get() as $mapping) {
            $idpelList = Pelanggan::where('kddk', $mapping->kddk)->pluck('idpel');

            $tunggakan = TagihanRekeningBulanan::whereIn('idpel', $idpelList)
                ->whereIn('thblrek', $bulan)
                ->where('status_lunas', false)
                ->get()
                ->groupBy('idpel');

            $bucket = [1 => ['plg' => 0, 'rp' => 0], 2 => ['plg' => 0, 'rp' => 0], 3 => ['plg' => 0, 'rp' => 0]];

            foreach ($tunggakan as $idpel => $tagihanList) {
                $lembar = min($tagihanList->count(), 3);
                $bucket[$lembar]['plg']++;
                $bucket[$lembar]['rp'] += $tagihanList->sum('rp_tag');
                $summary[$lembar]['plg']++;
                $summary[$lembar]['rp'] += $tagihanList->sum('rp_tag');
            }

            $rows[] = [
                'kddk' => $mapping->kddk,
                'petugas' => $mapping->petugas?->nama_petugas ?? '-',
                'posko' => $mapping->petugas?->posko?->nama_posko ?? '-',
                'bucket' => $bucket,
            ];
        }

        return view('dashboard.lembar', [
            'rows' => collect($rows)->sortBy('petugas')->values(),
            'summary' => $summary,
            'periode' => Carbon::now()->translatedFormat('F Y'),
        ]);
    }

    /**
     * Endpoint AJAX untuk modal drill-down: daftar pelanggan 2/3 lembar per KDDK.
     */
    public function detail(Request $request)
    {
        $request->validate([
            'kddk' => 'required|string',
            'lembar' => 'required|integer|min:1|max:3',
        ]);

        $bulan = $this->bulanRelevan();
        $idpelList = Pelanggan::where('kddk', $request->kddk)->pluck('idpel');

        $tunggakan = TagihanRekeningBulanan::whereIn('idpel', $idpelList)
            ->whereIn('thblrek', $bulan)
            ->where('status_lunas', false)
            ->get()
            ->groupBy('idpel')
            ->filter(fn ($list) => min($list->count(), 3) === (int) $request->lembar);

        $pelangganMap = Pelanggan::whereIn('idpel', $tunggakan->keys())->get()->keyBy('idpel');

        $hasil = $tunggakan->map(function ($tagihanList, $idpel) use ($pelangganMap) {
            $p = $pelangganMap->get($idpel);

            return [
                'idpel' => $idpel,
                'nama' => $p->nama ?? '-',
                'alamat' => $p->alamat ?? '-',
                'tarif' => $tagihanList->first()->tarif,
                'daya' => $tagihanList->first()->daya,
                'total_rp_tagihan' => $tagihanList->sum('rp_tag'),
                'unpaid_bills' => $tagihanList->sortBy('thblrek')->values()->map(fn ($t) => [
                    'thblrek' => $t->thblrek,
                    'rp_tag' => $t->rp_tag,
                ]),
            ];
        })->values();

        return response()->json([
            'kddk' => $request->kddk,
            'lembar' => (int) $request->lembar,
            'pelanggan' => $hasil,
        ]);
    }
}
