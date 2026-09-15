<?php

namespace App\Http\Controllers;

use App\Models\Pelanggan;
use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardKodeKelompokController extends Controller
{
    protected array $deskripsiKogol = [
        0 => 'Rumah Tangga (R1/R1M)',
        1 => 'Sosial (S1/S2)',
        2 => 'Bisnis (B1/B2)',
        3 => 'Industri (I1/I2/I3)',
        9 => 'Khusus (P1/P2)',
    ];

    protected array $rentang = [
        '1-5' => [1, 5],
        '6-10' => [6, 10],
        '11-15' => [11, 15],
        '16-20' => [16, 20],
        '>20' => [21, 31],
    ];

    public function index(Request $request)
    {
        $now = Carbon::now();
        $thblrekLalu = (int) $now->copy()->subMonthNoOverflow()->format('Ym');

        $trendPerKogol = [];
        $tableSummary = [];

        foreach ($this->deskripsiKogol as $kogol => $deskripsi) {
            $idpelKogol = Pelanggan::where('kogol', $kogol)->pluck('idpel');
            $totalPlg = $idpelKogol->count();

            $transaksi = TransaksiPembayaranHarian::whereIn('idpel', $idpelKogol)
                ->where('thblrek', $thblrekLalu)
                ->get(['tgl_transaksi']);

            $trend = [];
            foreach ($this->rentang as $label => [$min, $max]) {
                $jumlah = $transaksi->filter(fn ($t) => $t->tgl_transaksi->day >= $min && $t->tgl_transaksi->day <= $max)->count();
                $trend[] = [
                    'rentang' => $label,
                    'persen' => $totalPlg > 0 ? round(($jumlah / $totalPlg) * 100, 1) : 0,
                ];
            }
            $trendPerKogol['kogol_' . $kogol] = $trend;

            $lunasSebelum20 = $transaksi->where('tgl_transaksi.day', '<=', 20)->count();
            $lunasSetelah20 = $transaksi->count() - $lunasSebelum20;

            $tableSummary[] = [
                'kogol' => $kogol,
                'deskripsi' => $deskripsi,
                'total_plg' => $totalPlg,
                'lunas_sebelum_20' => $lunasSebelum20,
                'lunas_setelah_20' => $lunasSetelah20,
                'belum_lunas' => max($totalPlg - $transaksi->count(), 0),
            ];
        }

        return view('dashboard.kode-kelompok', [
            'trendPerKogol' => $trendPerKogol,
            'tableSummary' => $tableSummary,
            'deskripsiKogol' => $this->deskripsiKogol,
            'periode' => $now->copy()->subMonthNoOverflow()->translatedFormat('F Y'),
        ]);
    }
}
