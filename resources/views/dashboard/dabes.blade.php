@extends('layouts.app')
@section('title', 'Tagihan Dabes (Daya Besar >= 53 kVA)')

@section('content')
@if ($jumlahAlert > 0)
<div class="bg-amber-50 border border-amber-300 text-amber-800 rounded-lg px-4 py-3 text-sm font-medium">
    ⚠️ PERINGATAN: {{ $jumlahAlert }} pelanggan Dabes telah melewati tanggal rata-rata bayar tahunan dan belum lunas bulan ini!
</div>
@endif

<form method="GET" class="bg-white rounded-lg border p-3 flex flex-wrap gap-3 items-end text-sm">
    <div>
        <label class="block text-xs text-slate-500 mb-1">Status</label>
        <select name="status_lunas" onchange="this.form.submit()" class="border rounded px-2 py-1.5">
            <option value="">Semua</option>
            <option value="0" @selected($statusFilter === '0')>Belum Lunas</option>
            <option value="1" @selected($statusFilter === '1')>Lunas</option>
        </select>
    </div>
</form>

<div class="grid grid-cols-3 gap-3">
    <div class="bg-white rounded-lg border p-4"><p class="text-xs text-slate-500">Total Pelanggan Dabes</p><p class="text-lg font-semibold">{{ $totalDabes }} Plg</p></div>
    <div class="bg-white rounded-lg border p-4"><p class="text-xs text-slate-500">Belum Lunas</p><p class="text-lg font-semibold text-rose-600">{{ $totalBelumLunas }} Plg (Rp {{ number_format($nominalBelumLunas,0,',','.') }})</p></div>
    <div class="bg-white rounded-lg border p-4"><p class="text-xs text-slate-500">Lunas</p><p class="text-lg font-semibold text-emerald-600">{{ $totalDabes - $totalBelumLunas }} Plg</p></div>
</div>

<div class="bg-white rounded-lg border p-4" x-data="{ open: null }">
    <p class="text-sm font-medium mb-3">Tabel Monitoring Pelanggan Daya Besar</p>
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="text-left text-slate-500 border-b">
            <tr>
                <th class="py-2 pr-3">IDPEL</th><th class="py-2 pr-3">Nama</th><th class="py-2 pr-3">Tarif</th>
                <th class="py-2 pr-3">Daya</th><th class="py-2 pr-3">Tagihan</th><th class="py-2 pr-3">Avg Bayar</th>
                <th class="py-2 pr-3">Status</th><th class="py-2 pr-3">Alert</th><th class="py-2 pr-3"></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $i => $item)
                <tr class="border-b">
                    <td class="py-2 pr-3">{{ $item['idpel'] }}</td>
                    <td class="py-2 pr-3 font-medium">{{ $item['nama'] }}</td>
                    <td class="py-2 pr-3">{{ $item['tarif'] }}</td>
                    <td class="py-2 pr-3">{{ number_format($item['daya']/1000, 0) }} kVA</td>
                    <td class="py-2 pr-3">Rp {{ number_format($item['rp_tag'],0,',','.') }}</td>
                    <td class="py-2 pr-3">Tgl {{ $item['avg_tgl_bayar'] ?? '-' }}</td>
                    <td class="py-2 pr-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $item['status_lunas'] ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $item['status_lunas'] ? 'LUNAS' : 'BELUM' }}
                        </span>
                    </td>
                    <td class="py-2 pr-3">{{ $item['is_alert_overdue'] ? '⚠️' : '' }}</td>
                    <td class="py-2 pr-3">
                        <button @click="open = (open === {{ $i }} ? null : {{ $i }})" class="text-blue-600 text-xs underline">Lihat tren</button>
                    </td>
                </tr>
                <tr x-show="open === {{ $i }}" x-cloak>
                    <td colspan="9" class="bg-slate-50 p-3">
                        <p class="text-xs text-slate-500 mb-2">Tren tanggal bayar {{ $item['nama'] }} (12 bulan terakhir)</p>
                        <canvas id="trend-{{ $i }}" height="60"></canvas>
                        <script>
                            new Chart(document.getElementById('trend-{{ $i }}'), {
                                type: 'line',
                                data: {
                                    labels: @json($item['riwayat']->pluck('thblrek')),
                                    datasets: [{
                                        label: 'Tanggal Bayar',
                                        data: @json($item['riwayat']->pluck('hari')),
                                        borderColor: '#2563eb',
                                        tension: 0.3,
                                    }, {
                                        label: 'Rata-rata',
                                        data: @json(array_fill(0, count($item['riwayat']), $item['avg_tgl_bayar'])),
                                        borderColor: '#dc2626', borderDash: [6,4], pointRadius: 0,
                                    }],
                                },
                                options: { scales: { y: { min: 1, max: 31 } }, plugins: { legend: { position: 'bottom' } } },
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
