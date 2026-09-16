@extends('layouts.app')
@section('title', 'Posko & Petugas')

@section('content')
<!-- Header Section -->
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 text-[11px] font-bold text-[#0066cc] tracking-wider uppercase mb-1">
            <span class="w-1.5 h-1.5 rounded-full bg-[#0066cc]"></span>
            <span>UNIT PELAKSANA PELAYANAN PENAGIHAN • EVALUASI TARGET POSKO &amp; PETUGAS</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">DASHBOARD POSKO &amp; PETUGAS</h1>
        <p class="text-sm text-gray-500 mt-1 max-w-2xl">Monitoring performa harian &amp; evaluasi pencapaian target penagihan per posko dan petugas.</p>
    </div>
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-[#e0e0e0] shadow-sm self-start md:self-auto text-xs text-gray-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Sinkronisasi Terakhir: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong></span>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" class="rounded-2xl bg-white border border-[#e0e0e0] p-4 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
    <div class="flex flex-wrap items-center gap-3">
        <div class="relative min-w-[210px]">
            <select name="posko_id" onchange="this.form.submit()" class="w-full bg-[#f8f9fa] border border-[#e0e0e0] text-gray-800 text-xs font-medium rounded-full py-2 pl-4 pr-9 appearance-none focus:outline-none focus:ring-2 focus:ring-[#0066cc]/20 focus:border-[#0066cc] transition-all cursor-pointer">
                <option value="">Posko: Semua Posko</option>
                @foreach ($poskos as $p)
                    <option value="{{ $p->id_posko }}" @selected($poskoId == $p->id_posko)>Posko: {{ $p->nama_posko }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none text-gray-500 text-[18px]">expand_more</span>
        </div>
    </div>
    <div class="flex items-center gap-2 self-end lg:self-auto">
        <a href="{{ route('dashboard.posko-petugas') }}" class="border border-[#e0e0e0] hover:bg-gray-50 text-gray-600 text-xs font-medium px-4 py-2 rounded-full transition-colors">Reset Filter</a>
    </div>
</form>

<!-- KPI Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @php
        $progress = $summary['target_threshold_rp'] > 0 ? round(($summary['realisasi_bulanan_rp'] / $summary['target_threshold_rp']) * 100, 1) : 0;
        $gapPercent = $summary['target_threshold_rp'] > 0 ? round(($summary['gap_threshold_rp'] / $summary['target_threshold_rp']) * 100, 1) : 0;
    @endphp
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="flex flex-col"><span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Target Threshold</span>
                <span class="text-xl font-bold text-gray-900 tracking-tight mt-1">Rp {{ number_format($summary['target_threshold_rp'],0,',','.') }}</span></div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0066cc] flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">flag</span></div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500">Batas Threshold Tgl 20</div>
    </div>
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="flex flex-col"><span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Realisasi Bulanan</span>
                <span class="text-xl font-bold text-[#0066cc] tracking-tight mt-1">Rp {{ number_format($summary['realisasi_bulanan_rp'],0,',','.') }}</span></div>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0066cc] flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">payments</span></div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 space-y-1.5">
            <div class="flex justify-between text-[11px]"><span class="text-gray-500">Progress</span><span class="font-semibold text-gray-900">{{ $progress }}% dari target</span></div>
            <div class="w-full h-1.5 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-[#0066cc] rounded-full" style="width: {{ min($progress,100) }}%"></div></div>
        </div>
    </div>
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="flex flex-col"><span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Sisa Gap Target</span>
                <span class="text-xl font-bold text-gray-900 tracking-tight mt-1">Rp {{ number_format($summary['gap_threshold_rp'],0,',','.') }}</span></div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">pending_actions</span></div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-orange-700 bg-orange-50 px-2 py-0.5 rounded-md">Perlu ditagihkan</span>
            <span class="text-[11px] font-semibold text-gray-600">{{ $gapPercent }}% Gap</span>
        </div>
    </div>
    <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
        <div class="flex items-start justify-between">
            <div class="flex flex-col"><span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Realisasi Hari Ini</span>
                <span class="text-xl font-bold text-gray-900 tracking-tight mt-1">Rp {{ number_format($summary['realisasi_harian_rp'],0,',','.') }}</span></div>
            <div class="w-10 h-10 rounded-xl bg-green-50 text-emerald-600 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">trending_up</span></div>
        </div>
        <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-500">Tgl {{ now()->day }} {{ now()->translatedFormat('F') }}</div>
    </div>
</div>

<!-- Chart -->
<div class="rounded-2xl bg-white border border-[#e0e0e0] p-6 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-4 border-b border-gray-100 mb-4">
        <div>
            <h3 class="text-base font-bold text-gray-900">Grafik Realisasi Harian vs Garis Target Threshold Tgl 20</h3>
            <p class="text-xs text-gray-500 mt-0.5">Tren capaian pembayaran harian s.d. hari ini</p>
        </div>
    </div>
    <canvas id="chartHarian" height="90"></canvas>
</div>

<!-- Table -->
<div class="rounded-2xl bg-white border border-[#e0e0e0] shadow-sm overflow-hidden">
    <div class="p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-100">
        <div>
            <h3 class="text-base font-bold text-gray-900">Tabel Realisasi per Posko &amp; Petugas</h3>
            <p class="text-xs text-gray-500 mt-0.5">Rincian performa saldo penagihan dan pencapaian target individual</p>
        </div>
    </div>
    <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead>
            <tr class="bg-[#f8f9fa] text-[11px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                <th class="py-3 px-4 rounded-l-xl">Posko</th><th class="py-3 px-4">Petugas</th><th class="py-3 px-4">PBM</th>
                <th class="py-3 px-4 text-right">Saldo Awal</th><th class="py-3 px-4 text-right text-[#0066cc]">Realisasi</th>
                <th class="py-3 px-4 text-right">Target</th><th class="py-3 px-4 rounded-r-xl text-center">Gap %</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
            @forelse ($petugasRows as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3.5 px-4 font-medium text-gray-900"><span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-[#0066cc]"></span>{{ $row['posko'] }}</span></td>
                    <td class="py-3.5 px-4 font-bold text-gray-900">{{ $row['petugas'] }}</td>
                    <td class="py-3.5 px-4 text-gray-500">{{ $row['pbm'] }}</td>
                    <td class="py-3.5 px-4 text-right tabular-nums">Rp {{ number_format($row['saldo_awal_rp'],0,',','.') }}</td>
                    <td class="py-3.5 px-4 text-right tabular-nums font-bold text-[#0066cc]">Rp {{ number_format($row['realisasi_rp'],0,',','.') }}</td>
                    <td class="py-3.5 px-4 text-right tabular-nums text-gray-500">Rp {{ number_format($row['target_threshold_rp'],0,',','.') }}</td>
                    <td class="py-3.5 px-4 text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $row['gap_percentage'] > 50 ? 'bg-rose-100 text-rose-700' : 'bg-blue-100/80 text-blue-800' }}">{{ $row['gap_percentage'] }}%</span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-6 text-center text-gray-400">Belum ada data petugas untuk filter ini.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>

<script>
new Chart(document.getElementById('chartHarian'), {
    type: 'bar',
    data: {
        labels: @json(collect($chartHarian)->pluck('tgl')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d'))),
        datasets: [
            { label: 'Realisasi Harian (Rp)', data: @json(collect($chartHarian)->pluck('realisasi_rp')), backgroundColor: '#0066cc', borderRadius: 4 },
            { label: 'Target Threshold Tgl 20 (Rp/hari)', data: @json(collect($chartHarian)->pluck('target_line_rp')), type: 'line', borderColor: '#dc2626', borderDash: [6,4], pointRadius: 0 },
        ],
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }, scales: { y: { grid: { color: '#f0f0f0' } }, x: { grid: { display: false } } } },
});
</script>
@endsection
