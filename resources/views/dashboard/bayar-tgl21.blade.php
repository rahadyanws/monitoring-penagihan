@extends('layouts.app')
@section('title', 'Pelanggan Bayar Tanggal 21 ke Atas')

@section('content')
<form method="GET" class="bg-white rounded-lg border p-3 flex flex-wrap gap-3 items-end text-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Bulan Rekening</label>
        <input type="month" name="thblrek_input" value="{{ substr($thblrek,0,4).'-'.substr($thblrek,4,2) }}"
               onchange="this.form.thblrek.value = this.value.replace('-',''); this.form.submit()" class="border rounded px-2 py-1.5">
        <input type="hidden" name="thblrek" value="{{ $thblrek }}">
    </div>
    <a href="{{ route('dashboard.bayar-tgl21.export', ['thblrek' => $thblrek]) }}"
       class="ml-auto bg-emerald-600 text-white text-xs px-3 py-2 rounded-md">⬇ Unduh Excel (.csv)</a>
</form>

<div class="bg-white rounded-lg border p-4 flex flex-wrap gap-6 text-sm">
    <div><p class="text-xs text-slate-500">Total Pelanggan Telat</p><p class="font-semibold">{{ number_format($totalPelanggan) }} Plg</p></div>
    <div><p class="text-xs text-slate-500">Total Pokok</p><p class="font-semibold">Rp {{ number_format($totalPokok,0,',','.') }}</p></div>
    <div><p class="text-xs text-slate-500">Total Denda BK</p><p class="font-semibold text-rose-600">Rp {{ number_format($totalBk,0,',','.') }}</p></div>
</div>

<div class="bg-white rounded-lg border p-4">
    <p class="text-sm font-medium mb-3">Tabel Audit Pelanggan Telat Bayar</p>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b">
            <tr><th class="py-2 pr-3">IDPEL</th><th class="py-2 pr-3">Nama</th><th class="py-2 pr-3">Tarif</th>
                <th class="py-2 pr-3">Daya</th><th class="py-2 pr-3">Tgl Bayar</th><th class="py-2 pr-3">Tagihan</th><th class="py-2 pr-3">BK</th></tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr class="border-b">
                    <td class="py-2 pr-3">{{ $r->idpel }}</td>
                    <td class="py-2 pr-3 font-medium">{{ $r->pelanggan->nama ?? '-' }}</td>
                    <td class="py-2 pr-3">{{ $r->tarif }}</td>
                    <td class="py-2 pr-3">{{ $r->daya }}</td>
                    <td class="py-2 pr-3">{{ $r->tgl_transaksi->format('Y-m-d') }}</td>
                    <td class="py-2 pr-3">Rp {{ number_format($r->rp_tagihan,0,',','.') }}</td>
                    <td class="py-2 pr-3">Rp {{ number_format($r->rp_bk,0,',','.') }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-4 text-center text-slate-400">Tidak ada pelanggan telat bayar pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="mt-3">{{ $rows->links() }}</div>
</div>
@endsection
