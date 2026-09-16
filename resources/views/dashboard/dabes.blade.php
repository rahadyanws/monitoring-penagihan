@extends('layouts.app')
@section('title', 'Tagihan Dabes')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 text-[11px] font-bold text-[#0066cc] tracking-wider uppercase mb-1">
            <span class="w-1.5 h-1.5 rounded-full bg-[#0066cc]"></span>
            <span>UNIT PELAKSANA PELAYANAN PENAGIHAN • SEGMENTASI KONSUMEN PRIORITAS</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">DASHBOARD TAGIHAN DABES (&ge; 53 kVA)</h1>
        <p class="text-sm text-gray-500 mt-1 max-w-2xl">Monitoring tagihan pelanggan potensial, riwayat kebiasaan bayar, dan deteksi keterlambatan.</p>
    </div>
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-[#e0e0e0] shadow-sm self-start md:self-auto text-xs text-gray-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Sinkronisasi Terakhir: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong></span>
    </div>
</div>

@if ($jumlahAlert > 0)
<div class="rounded-2xl p-4 bg-amber-50/80 border border-amber-200 flex flex-col md:flex-row md:items-center justify-between gap-4 text-amber-950">
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-amber-500/15 border border-amber-400/30 flex items-center justify-center shrink-0 mt-0.5">
            <span class="material-symbols-outlined text-amber-700 text-xl">warning</span>
        </div>
        <div>
            <div class="flex items-center gap-2">
                <span class="text-xs font-extrabold tracking-wide uppercase text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded">PERINGATAN KRITIS</span>
                <span class="text-xs font-bold text-amber-900">{{ $jumlahAlert }} Pelanggan Daya Besar Melewati Tanggal Rata-rata Bayar</span>
            </div>
            <p class="text-xs text-amber-800 mt-1 leading-relaxed max-w-4xl">Segera dilakukan tindak lanjut penagihan / kunjungan lapangan oleh Tim TL / Posko terkait sebelum batas threshold tanggal 20.</p>
        </div>
    </div>
</div>
@endif

<form method="GET" class="rounded-2xl bg-white border border-[#e0e0e0] p-4 shadow-sm flex flex-wrap items-center gap-3">
    <div class="relative min-w-[210px]">
        <select name="status_lunas" onchange="this.form.submit()" class="w-full bg-[#f8f9fa] border border-[#e0e0e0] text-gray-800 text-xs font-medium rounded-full py-2 pl-4 pr-9 appearance-none focus:outline-none focus:ring-2 focus:ring-[#0066cc]/20 cursor-pointer">
            <option value="">Status: Semua</option>
            <option value="0" @selected($statusFilter === '0')>Status: Belum Lunas</option>
            <option value="1" @selected($statusFilter === '1')>Status: Lunas</option>
        </select>
        <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 text-[18px]">expand_more</span>
    </div>
</form>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
    <div class="rounded-2xl bg-white p-5 border border-[#e0e0e0] shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Total Pelanggan Dabes</span>
            <div class="w-8 h-8 rounded-full bg-blue-50 text-[#0066cc] flex items-center justify-center"><span class="material-symbols-outlined text-base">factory</span></div>
        </div>
        <div class="flex items-baseline gap-2"><span class="text-2xl font-bold text-gray-900">{{ $totalDabes }}</span><span class="text-sm font-semibold text-gray-500">Pelanggan</span></div>
    </div>
    <div class="rounded-2xl bg-white p-5 border border-amber-200 shadow-sm ring-1 ring-amber-100">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Belum Lunas</span>
            <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center"><span class="material-symbols-outlined text-base">pending_actions</span></div>
        </div>
        <div class="flex items-baseline gap-2"><span class="text-2xl font-bold text-amber-900">{{ $totalBelumLunas }}</span><span class="text-sm font-semibold text-gray-500">Plg</span></div>
        <div class="mt-2 text-xs text-amber-700 font-semibold">Rp {{ number_format($nominalBelumLunas,0,',','.') }}</div>
    </div>
    <div class="rounded-2xl bg-white p-5 border border-[#e0e0e0] shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Realisasi Lunas</span>
            <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center"><span class="material-symbols-outlined text-base">check_circle</span></div>
        </div>
        <div class="flex items-baseline gap-2"><span class="text-2xl font-bold text-gray-900">{{ $totalDabes - $totalBelumLunas }}</span><span class="text-sm font-semibold text-gray-500">Plg</span></div>
    </div>
</div>

<div class="rounded-2xl bg-white border border-[#e0e0e0] shadow-sm overflow-hidden" x-data="{ open: null }">
    <div class="p-5 border-b border-gray-100">
        <h2 class="text-base font-bold text-gray-900">Daftar Monitoring Pelanggan Daya Besar</h2>
        <p class="text-xs text-gray-500 mt-0.5">Klik "Lihat tren" untuk memeriksa riwayat 12 bulan pembayaran</p>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead>
            <tr class="bg-[#f8f9fa] text-[11px] font-bold uppercase tracking-wider text-gray-500 border-b border-gray-200">
                <th class="py-3 px-4">IDPEL</th><th class="py-3 px-4">Nama Pelanggan</th><th class="py-3 px-3 text-center">Tarif</th>
                <th class="py-3 px-3 text-right">Daya</th><th class="py-3 px-4 text-right">Tagihan</th><th class="py-3 px-3 text-center">Avg Bayar</th>
                <th class="py-3 px-4 text-center">Status</th><th class="py-3 px-4">Alert</th><th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach ($items as $i => $item)
                <tr class="hover:bg-gray-50 transition-colors {{ $item['is_alert_overdue'] ? 'bg-rose-50/30' : '' }}">
                    <td class="py-3.5 px-4 font-mono font-bold text-gray-900">{{ $item['idpel'] }}</td>
                    <td class="py-3.5 px-4 font-bold text-gray-900">{{ $item['nama'] }}</td>
                    <td class="py-3.5 px-3 text-center"><span class="px-2 py-0.5 rounded bg-gray-100 font-bold text-gray-700">{{ $item['tarif'] }}</span></td>
                    <td class="py-3.5 px-3 text-right font-medium text-gray-800">{{ number_format($item['daya']/1000,0) }} kVA</td>
                    <td class="py-3.5 px-4 text-right font-mono font-bold text-gray-900">Rp {{ number_format($item['rp_tag'],0,',','.') }}</td>
                    <td class="py-3.5 px-3 text-center font-semibold text-gray-700">Tgl {{ $item['avg_tgl_bayar'] ?? '-' }}</td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold {{ $item['status_lunas'] ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $item['status_lunas'] ? 'LUNAS' : 'BELUM LUNAS' }}</span>
                    </td>
                    <td class="py-3.5 px-4">
                        @if ($item['is_alert_overdue'])
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100/80 text-amber-900 border border-amber-200">
                                <span class="material-symbols-outlined text-xs text-amber-700">warning</span><span>Lewat Avg</span>
                            </span>
                        @endif
                    </td>
                    <td class="py-3.5 px-4"><button @click="open = (open === {{ $i }} ? null : {{ $i }})" class="text-[#0066cc] text-xs font-semibold underline">Lihat tren</button></td>
                </tr>
                <tr x-show="open === {{ $i }}" x-cloak>
                    <td colspan="9" class="bg-[#f8f9fa] p-4">
                        <p class="text-xs text-gray-500 mb-2">Tren tanggal bayar {{ $item['nama'] }} (12 bulan terakhir)</p>
                        <canvas id="trend-{{ $i }}" height="60"></canvas>
                        <script>
                            new Chart(document.getElementById('trend-{{ $i }}'), {
                                type: 'line',
                                data: {
                                    labels: @json($item['riwayat']->pluck('thblrek')),
                                    datasets: [
                                        { label: 'Tanggal Bayar', data: @json($item['riwayat']->pluck('hari')), borderColor: '#0066cc', tension: 0.3 },
                                        { label: 'Rata-rata', data: @json(array_fill(0, count($item['riwayat']), $item['avg_tgl_bayar'])), borderColor: '#dc2626', borderDash: [6,4], pointRadius: 0 },
                                    ],
                                },
                                options: { scales: { y: { min: 1, max: 31 } }, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } } },
                            });
                        </script>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>
@endsection
