@extends('layouts.app')
@section('title', 'Posko & Petugas')

@section('content')
<form method="GET" class="bg-white rounded-lg border p-3 flex flex-wrap gap-3 items-end text-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Posko</label>
        <select name="posko_id" onchange="this.form.submit()" class="border rounded px-2 py-1.5">
            <option value="">Semua Posko</option>
            @foreach ($poskos as $p)
                <option value="{{ $p->id_posko }}" @selected($poskoId == $p->id_posko)>{{ $p->nama_posko }}</option>
            @endforeach
        </select>
    </div>
</form>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
    @foreach ([
        ['Target Threshold', $summary['target_threshold_rp'], 'text-slate-700'],
        ['Realisasi Bulanan', $summary['realisasi_bulanan_rp'], 'text-emerald-600'],
        ['Gap Target Sisa', $summary['gap_threshold_rp'], 'text-rose-600'],
        ['Realisasi Hari Ini', $summary['realisasi_harian_rp'], 'text-blue-600'],
    ] as [$label, $value, $color])
        <div class="bg-white rounded-lg border p-4">
            <p class="text-xs text-slate-500">{{ $label }}</p>
            <p class="text-lg font-semibold {{ $color }}">Rp {{ number_format($value, 0, ',', '.') }}</p>
        </div>
    @endforeach
</div>

<div class="bg-white rounded-lg border p-4">
    <p class="text-sm font-medium mb-2">Grafik Realisasi Harian vs Garis Target Threshold Tgl 20</p>
    <canvas id="chartHarian" height="90"></canvas>
</div>

<div class="bg-white rounded-lg border p-4">
    <p class="text-sm font-medium mb-3">Tabel Realisasi per Posko & Petugas</p>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b">
            <tr>
                <th class="py-2 pr-3">Posko</th>
                <th class="py-2 pr-3">Petugas</th>
                <th class="py-2 pr-3">PBM</th>
                <th class="py-2 pr-3">Saldo Awal</th>
                <th class="py-2 pr-3">Realisasi</th>
                <th class="py-2 pr-3">Target</th>
                <th class="py-2 pr-3">Gap %</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($petugasRows as $row)
                <tr class="border-b last:border-0">
                    <td class="py-2 pr-3">{{ $row['posko'] }}</td>
                    <td class="py-2 pr-3 font-medium">{{ $row['petugas'] }}</td>
                    <td class="py-2 pr-3">{{ $row['pbm'] }}</td>
                    <td class="py-2 pr-3">Rp {{ number_format($row['saldo_awal_rp'], 0, ',', '.') }}</td>
                    <td class="py-2 pr-3 text-emerald-600">Rp {{ number_format($row['realisasi_rp'], 0, ',', '.') }}</td>
                    <td class="py-2 pr-3">Rp {{ number_format($row['target_threshold_rp'], 0, ',', '.') }}</td>
                    <td class="py-2 pr-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $row['gap_percentage'] > 50 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $row['gap_percentage'] }}%
                        </span>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="py-4 text-center text-slate-400">Belum ada data petugas untuk filter ini.</td></tr>
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
            {
                label: 'Realisasi Harian (Rp)',
                data: @json(collect($chartHarian)->pluck('realisasi_rp')),
                backgroundColor: '#2563eb',
            },
            {
                label: 'Target Threshold Tgl 20 (Rp/hari)',
                data: @json(collect($chartHarian)->pluck('target_line_rp')),
                type: 'line',
                borderColor: '#dc2626',
                borderDash: [6, 4],
                pointRadius: 0,
            },
        ],
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } },
});
</script>
@endsection
