@extends('layouts.app')
@section('title', 'Tren Kode Kelompok')

@section('content')
<div class="bg-white rounded-lg border p-4">
    <p class="text-sm font-medium mb-2">Distribusi Tanggal Pembayaran per Kode Kelompok (Periode {{ $periode }})</p>
    <canvas id="chartKogol" height="90"></canvas>
</div>

<div class="bg-white rounded-lg border p-4">
    <p class="text-sm font-medium mb-3">Tabel Distribusi Realisasi Kelompok</p>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b">
            <tr>
                <th class="py-2 pr-3">Kode Kel</th><th class="py-2 pr-3">Deskripsi</th><th class="py-2 pr-3">Total Plg</th>
                <th class="py-2 pr-3">Lunas &le; Tgl 20</th><th class="py-2 pr-3">Lunas &gt; Tgl 20</th><th class="py-2 pr-3">Belum Lunas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($tableSummary as $row)
                <tr class="border-b">
                    <td class="py-2 pr-3">Kogol {{ $row['kogol'] }}</td>
                    <td class="py-2 pr-3">{{ $row['deskripsi'] }}</td>
                    <td class="py-2 pr-3">{{ number_format($row['total_plg']) }} Plg</td>
                    <td class="py-2 pr-3 text-emerald-600">{{ number_format($row['lunas_sebelum_20']) }}</td>
                    <td class="py-2 pr-3 text-amber-600">{{ number_format($row['lunas_setelah_20']) }}</td>
                    <td class="py-2 pr-3 text-rose-600">{{ number_format($row['belum_lunas']) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</div>

<script>
const rentang = {!! json_encode(array_keys(['1-5'=>1,'6-10'=>1,'11-15'=>1,'16-20'=>1,'>20'=>1])) !!};
const trend = @json($trendPerKogol);
const deskripsi = @json($deskripsiKogol);
const colors = { kogol_0: '#2563eb', kogol_1: '#7c3aed', kogol_2: '#059669', kogol_3: '#d97706', kogol_9: '#dc2626' };

new Chart(document.getElementById('chartKogol'), {
    type: 'line',
    data: {
        labels: rentang.map(r => 'Tgl ' + r),
        datasets: Object.keys(trend).map(key => ({
            label: deskripsi[key.replace('kogol_', '')] ?? key,
            data: trend[key].map(t => t.persen),
            borderColor: colors[key] || '#334155',
            tension: 0.3,
        })),
    },
    options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { title: { display: true, text: '% Bayar' } } } },
});
</script>
@endsection
