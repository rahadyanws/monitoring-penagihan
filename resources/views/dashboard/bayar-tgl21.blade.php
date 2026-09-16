@extends('layouts.app')
@section('title', 'Bayar Tgl 21+')

@section('content')
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 text-[11px] font-bold text-[#0066cc] tracking-wider uppercase mb-1">
            <span class="w-1.5 h-1.5 rounded-full bg-[#0066cc]"></span>
            <span>UNIT PELAKSANA PELAYANAN PENAGIHAN • EVALUASI KEPATUHAN &amp; BIAYA KETERLAMBATAN</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">PELANGGAN BAYAR TANGGAL 21 KE ATAS</h1>
        <p class="text-sm text-gray-500 mt-1 max-w-2xl">Audit pelanggan yang melunasi tagihan melewati cut-off (Tgl 20) beserta akumulasi Biaya Keterlambatan (BK).</p>
    </div>
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-[#e0e0e0] shadow-sm self-start lg:self-end text-xs text-gray-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Sinkronisasi Terakhir: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong></span>
    </div>
</div>

<form method="GET" class="rounded-2xl bg-white border border-[#e0e0e0] p-4 shadow-sm flex flex-wrap items-center gap-3">
    <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-[#f8f9fa] border border-[#e0e0e0] text-xs">
        <span class="material-symbols-outlined text-[17px] text-gray-400">calendar_today</span>
        <span class="text-gray-400 font-medium">Bulan:</span>
        <input type="month" name="thblrek_input" value="{{ substr($thblrek,0,4).'-'.substr($thblrek,4,2) }}"
               onchange="this.form.thblrek.value = this.value.replace('-',''); this.form.submit()" class="bg-transparent font-semibold text-gray-800 outline-none border-none p-0 focus:ring-0">
        <input type="hidden" name="thblrek" value="{{ $thblrek }}">
    </div>
    <a href="{{ route('dashboard.bayar-tgl21.export', ['thblrek' => $thblrek]) }}"
       class="ml-auto bg-[#0066cc] hover:bg-[#0052a3] text-white text-xs font-semibold px-4 py-2.5 rounded-full flex items-center gap-1.5 transition-colors">
        <span class="material-symbols-outlined text-[16px]">file_download</span><span>Unduh Excel (.csv)</span>
    </a>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm">
        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pelanggan Telat</span>
        <div class="text-xl font-bold text-gray-900 mt-1">{{ number_format($totalPelanggan) }} Plg</div>
    </div>
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm">
        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pokok</span>
        <div class="text-xl font-bold text-gray-900 mt-1">Rp {{ number_format($totalPokok,0,',','.') }}</div>
    </div>
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm">
        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Denda BK</span>
        <div class="text-xl font-bold text-rose-600 mt-1">Rp {{ number_format($totalBk,0,',','.') }}</div>
    </div>
</div>

<div class="rounded-2xl bg-white border border-[#e0e0e0] shadow-sm overflow-hidden">
    <div class="p-5 border-b border-gray-100"><h2 class="text-base font-bold text-gray-900">Tabel Audit Pelanggan Telat Bayar</h2></div>
    <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead>
            <tr class="bg-[#f8f9fa] text-[11px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                <th class="py-3 px-4">IDPEL</th><th class="py-3 px-4">Nama</th><th class="py-3 px-3 text-center">Tarif</th>
                <th class="py-3 px-3 text-right">Daya</th><th class="py-3 px-4">Tgl Bayar</th><th class="py-3 px-4 text-right">Tagihan</th><th class="py-3 px-4 text-right">BK</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
            @forelse ($rows as $r)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3.5 px-4 font-mono font-medium text-gray-800">{{ $r->idpel }}</td>
                    <td class="py-3.5 px-4 font-bold text-gray-900">{{ $r->pelanggan->nama ?? '-' }}</td>
                    <td class="py-3.5 px-3 text-center"><span class="px-2 py-0.5 rounded bg-gray-100 font-bold text-gray-700">{{ $r->tarif }}</span></td>
                    <td class="py-3.5 px-3 text-right">{{ $r->daya }}</td>
                    <td class="py-3.5 px-4"><span class="inline-flex items-center px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-semibold text-[11px]">{{ $r->tgl_transaksi->format('d M Y') }}</span></td>
                    <td class="py-3.5 px-4 text-right font-mono">Rp {{ number_format($r->rp_tagihan,0,',','.') }}</td>
                    <td class="py-3.5 px-4 text-right font-mono text-rose-600 font-semibold">Rp {{ number_format($r->rp_bk,0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-center text-gray-400">Tidak ada pelanggan telat bayar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="p-4 border-t border-gray-100">{{ $rows->links() }}</div>
</div>
@endsection
