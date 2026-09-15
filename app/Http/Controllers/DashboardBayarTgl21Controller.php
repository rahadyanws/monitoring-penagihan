<?php

namespace App\Http\Controllers;

use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardBayarTgl21Controller extends Controller
{
    protected function query(int $thblrek)
    {
        // Mendukung PostgreSQL & MySQL (lihat SETUP.md - kedua DB didukung).
        $driver = DB::connection()->getDriverName();
        $ekspresiHari = $driver === 'pgsql'
            ? 'EXTRACT(DAY FROM tgl_transaksi) >= 21'
            : 'DAY(tgl_transaksi) >= 21';

        return TransaksiPembayaranHarian::with('pelanggan')
            ->where('thblrek', $thblrek)
            ->whereRaw($ekspresiHari)
            ->orderByDesc('tgl_transaksi');
    }

    public function index(Request $request)
    {
        $now = Carbon::now();
        $thblrek = (int) $request->input('thblrek', $now->copy()->subMonthNoOverflow()->format('Ym'));

        $data = $this->query($thblrek)->paginate(25)->withQueryString();

        $totalPelanggan = (clone $data)->total();
        $agregat = $this->query($thblrek)->getQuery()->clone()
            ->reorder()
            ->selectRaw('COUNT(*) as jml, COALESCE(SUM(rp_tagihan),0) as total_pokok, COALESCE(SUM(rp_bk),0) as total_bk')
            ->first();

        return view('dashboard.bayar-tgl21', [
            'rows' => $data,
            'thblrek' => $thblrek,
            'totalPelanggan' => $agregat->jml ?? 0,
            'totalPokok' => $agregat->total_pokok ?? 0,
            'totalBk' => $agregat->total_bk ?? 0,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $now = Carbon::now();
        $thblrek = (int) $request->input('thblrek', $now->copy()->subMonthNoOverflow()->format('Ym'));
        $rows = $this->query($thblrek)->get();

        $filename = "Pelanggan_Bayar_Tgl_21_Keatas_{$thblrek}.csv";

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['IDPEL', 'Nama', 'Tarif', 'Daya', 'Tgl Bayar', 'Tagihan (Rp)', 'BK (Rp)']);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->idpel,
                    $r->pelanggan->nama ?? '-',
                    $r->tarif,
                    $r->daya,
                    $r->tgl_transaksi->format('Y-m-d'),
                    $r->rp_tagihan,
                    $r->rp_bk,
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);

        // CATATAN: untuk output .xlsx asli (bukan CSV), tinggal ganti implementasi ini
        // memakai package `maatwebsite/excel` sesuai rencana di ARCHITECTURE.md/ROADMAP.md.
    }
}
