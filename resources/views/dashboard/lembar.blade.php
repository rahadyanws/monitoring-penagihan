@extends('layouts.app')
@section('title', 'Saldo Lembar')

@section('content')
<div x-data="lembarModal()">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-[11px] font-bold text-[#0066cc] tracking-wider uppercase mb-1">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0066cc]"></span>
                <span>UNIT PELAKSANA PELAYANAN PENAGIHAN • MITIGASI TUNGGAKAN</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-gray-900">SALDO TUNGGAKAN LEMBAR</h1>
            <p class="text-sm text-gray-500 mt-1 max-w-2xl">Rekapitulasi pelanggan menunggak 1, 2, dan 3 lembar rekening per petugas.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
        @foreach ([1 => ['1 LEMBAR', 'Bulan Berjalan', 'blue'], 2 => ['2 LEMBAR', 'Nunggak 2 Bulan', 'amber'], 3 => ['3 LEMBAR', 'Nunggak &ge;3 Bulan', 'rose']] as $n => [$label, $sub, $c])
            <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <span class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ $label }}</span>
                        <p class="text-[11px] text-gray-400">{!! $sub !!}</p>
                        <div class="text-xl font-bold text-gray-900 mt-2">{{ number_format($summary[$n]['plg']) }} Plg</div>
                        <div class="text-sm text-gray-500">Rp {{ number_format($summary[$n]['rp'],0,',','.') }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-{{ $c }}-50 text-{{ $c }}-600 flex items-center justify-center"><span class="material-symbols-outlined text-[20px]">description</span></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl bg-white border border-[#e0e0e0] shadow-sm overflow-hidden mt-4">
        <div class="p-5 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Tabel Monitoring Saldo Lembar per Petugas</h2>
            <p class="text-xs text-gray-400 mt-0.5">Klik angka pada kolom 2 Lembar / 3 Lembar untuk melihat rincian pelanggan</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            <thead>
                <tr class="bg-[#f8f9fa] text-[11px] font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-200">
                    <th class="py-3 px-4">Petugas</th><th class="py-3 px-4">Posko</th><th class="py-3 px-4">KDDK</th>
                    <th class="py-3 px-4">1 Lembar</th><th class="py-3 px-4">2 Lembar</th><th class="py-3 px-4">3 Lembar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
                @foreach ($rows as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="py-3.5 px-4 font-bold text-gray-900">{{ $row['petugas'] }}</td>
                        <td class="py-3.5 px-4">{{ $row['posko'] }}</td>
                        <td class="py-3.5 px-4 font-mono">{{ $row['kddk'] }}</td>
                        <td class="py-3.5 px-4">{{ $row['bucket'][1]['plg'] }} / Rp {{ number_format($row['bucket'][1]['rp'],0,',','.') }}</td>
                        <td class="py-3.5 px-4">
                            @if ($row['bucket'][2]['plg'] > 0)
                                <button @click="buka('{{ $row['kddk'] }}', 2, '{{ $row['petugas'] }}')" class="text-[#0066cc] font-semibold underline">{{ $row['bucket'][2]['plg'] }} / Rp {{ number_format($row['bucket'][2]['rp'],0,',','.') }}</button>
                            @else <span class="text-gray-300">-</span> @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @if ($row['bucket'][3]['plg'] > 0)
                                <button @click="buka('{{ $row['kddk'] }}', 3, '{{ $row['petugas'] }}')" class="text-[#0066cc] font-semibold underline">{{ $row['bucket'][3]['plg'] }} / Rp {{ number_format($row['bucket'][3]['rp'],0,',','.') }}</button>
                            @else <span class="text-gray-300">-</span> @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[80vh] overflow-y-auto p-6 shadow-xl" @click.outside="modalOpen = false">
            <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                <p class="font-bold text-sm text-gray-900">Rincian Tunggakan — <span x-text="petugasNama"></span> (<span x-text="lembar"></span> Lembar)</p>
                <button @click="modalOpen = false" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-500"><span class="material-symbols-outlined text-[18px]">close</span></button>
            </div>
            <template x-if="loading"><p class="text-sm text-gray-400">Memuat data...</p></template>
            <template x-for="p in pelangganList" :key="p.idpel">
                <div class="border-b border-gray-100 py-3 text-sm">
                    <p class="font-bold text-gray-900" x-text="p.nama"></p>
                    <p class="text-xs text-gray-500" x-text="'IDPEL: ' + p.idpel + ' — ' + p.tarif + ' / ' + p.daya + ' VA'"></p>
                    <p class="text-xs text-gray-500" x-text="p.alamat"></p>
                    <ul class="text-xs mt-1.5 space-y-0.5">
                        <template x-for="b in p.unpaid_bills" :key="b.thblrek">
                            <li class="flex justify-between text-gray-600"><span x-text="'Rekening ' + b.thblrek"></span><span class="font-mono" x-text="'Rp ' + Number(b.rp_tag).toLocaleString('id-ID')"></span></li>
                        </template>
                    </ul>
                    <p class="text-xs font-bold text-rose-600 mt-1.5" x-text="'Total Tunggakan: Rp ' + Number(p.total_rp_tagihan).toLocaleString('id-ID')"></p>
                </div>
            </template>
        </div>
    </div>
</div>

<script>
function lembarModal() {
    return {
        modalOpen: false, loading: false, petugasNama: '', lembar: null, pelangganList: [],
        async buka(kddk, lembar, petugasNama) {
            this.modalOpen = true; this.loading = true; this.petugasNama = petugasNama; this.lembar = lembar;
            const res = await fetch(`{{ route('dashboard.lembar.detail') }}?kddk=${kddk}&lembar=${lembar}`);
            const data = await res.json();
            this.pelangganList = data.pelanggan; this.loading = false;
        },
    };
}
</script>
@endsection
