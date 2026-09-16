@extends('layouts.app')
@section('title', 'Tren Kode Kelompok')

@section('content')
<div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-2 text-[11px] font-bold text-[#0066cc] tracking-wider uppercase mb-1">
            <span class="w-1.5 h-1.5 rounded-full bg-[#0066cc]"></span>
            <span>UNIT PELAKSANA PELAYANAN PENAGIHAN • POLA PEMBAYARAN KONSUMEN</span>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">DASHBOARD TREN KODE KELOMPOK</h1>
        <p class="text-sm text-gray-500 mt-1 max-w-2xl">Analisis tren waktu pelunasan per kelompok pelanggan (Kogol) periode {{ $periode }}.</p>
    </div>
    <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-white border border-[#e0e0e0] shadow-sm self-start md:self-auto text-xs text-gray-600">
        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
        <span>Sinkronisasi Terakhir: <strong>{{ now()->format('d M Y, H:i') }} WIB</strong></span>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
    @php
        $iconKogol = [0 => 'home', 1 => 'volunteer_activism', 2 => 'storefront', 3 => 'factory', 9 => 'account_balance'];
        $colorKogol = [0 => 'blue', 1 => 'amber', 2 => 'indigo', 3 => 'emerald', 9 => 'rose'];
    @endphp
    @foreach ($tableSummary as $row)
        @php $c = $colorKogol[$row['kogol']] ?? 'gray'; @endphp
        <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex flex-col">
                    <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">KOGOL {{ $row['kogol'] }}</span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-2xl font-bold text-gray-900 tracking-tight">{{ number_format($row['total_plg']) }}</span>
                        <span class="text-xs font-medium text-gray-500">Plg</span>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-{{ $c }}-50 text-{{ $c }}-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[22px]">{{ $iconKogol[$row['kogol']] ?? 'category' }}</span>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-gray-100 text-[11px] text-gray-600">{{ $row['deskripsi'] }}</div>
        </div>
    @endforeach
</div>

<div class="rounded-2xl bg-white border border-[#e0e0e0] p-6 shadow-sm">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-100 mb-4">
        <div>
            <h2 class="text-base font-bold text-gray-900">Grafik Distribusi Pembayaran per Kode Kelompok (% per Interval)</h2>
            <p class="text-xs text-gray-500 mt-0.5">Sebaran tanggal bayar 1 - 31, area merah = masa denda (&gt; Tgl 20)</p>
        </div>
    </div>
    <canvas id="chartKogol" height="90"></canvas>
</div>

<div class="rounded-2xl bg-white border border-[#e0e0e0] p-6 shadow-sm">
    <h2 class="text-base font-bold text-gray-900 mb-1">Distribusi Transaksi Kelompok</h2>
    <p class="text-xs text-gray-500 mb-4">Rincian status pelunasan pelanggan per kode golongan rekening</p>
    <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead>
            <tr class="bg-[#f8f9fa] text-[11px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                <th class="py-3 px-4 rounded-l-xl">Kode Kel</th><th class="py-3 px-4">Deskripsi</th><th class="py-3 px-4 text-right">Total Plg</th>
                <th class="py-3 px-4 text-right">Lunas &le; Tgl 20</th><th class="py-3 px-4 text-right">Lunas &gt; Tgl 20</th><th class="py-3 px-4 rounded-r-xl text-right">Belum Lunas</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 text-gray-700">
            @foreach ($tableSummary as $row)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-3.5 px-4 font-bold text-gray-900"><span class="px-2.5 py-1 rounded-md bg-blue-50 text-[#0066cc] font-semibold text-[11px]">Kogol {{ $row['kogol'] }}</span></td>
                    <td class="py-3.5 px-4"><div class="font-medium text-gray-900">{{ $row['deskripsi'] }}</div></td>
                    <td class="py-3.5 px-4 text-right font-semibold text-gray-900 tabular-nums">{{ number_format($row['total_plg']) }} Plg</td>
                    <td class="py-3.5 px-4 text-right text-blue-600 font-semibold tabular-nums">{{ number_format($row['lunas_sebelum_20']) }}</td>
                    <td class="py-3.5 px-4 text-right text-amber-600 font-semibold tabular-nums">{{ number_format($row['lunas_setelah_20']) }}</td>
                    <td class="py-3.5 px-4 text-right text-red-600 font-semibold tabular-nums">{{ number_format($row['belum_lunas']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<script>
const trend = @json($trendPerKogol);
const deskripsi = @json($deskripsiKogol);
const colors = { kogol_0: '#0066cc', kogol_1: '#d97706', kogol_2: '#4f46e5', kogol_3: '#059669', kogol_9: '#e11d48' };
new Chart(document.getElementById('chartKogol'), {
    type: 'line',
    data: {
        labels: ['Tgl 1-5', 'Tgl 6-10', 'Tgl 11-15', 'Tgl 16-20', 'Tgl > 20'],
        datasets: Object.keys(trend).map(key => ({
            label: 'Kogol ' + key.replace('kogol_', '') + ' — ' + (deskripsi[key.replace('kogol_', '')] ?? ''),
            data: trend[key].map(t => t.persen),
            borderColor: colors[key] || '#334155',
            tension: 0.35,
        })),
    },
    options: { plugins: { legend: { position: 'bottom', labels: { font: { size: 10 } } } }, scales: { y: { title: { display: true, text: '% Bayar' }, grid: { color: '#f0f0f0' } }, x: { grid: { display: false } } } },
});
</script>
@endsection
