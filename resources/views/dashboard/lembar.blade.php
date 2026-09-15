@extends('layouts.app')
@section('title', 'Saldo Tunggakan Lembar')

@section('content')
<div x-data="lembarModal()">
    <div class="grid grid-cols-3 gap-3">
        @foreach ([1 => '1 LEMBAR (Bln Berjalan)', 2 => '2 LEMBAR (Nunggak 2 Bln)', 3 => '3 LEMBAR (Nunggak >=3 Bln)'] as $n => $label)
            <div class="bg-white rounded-lg border p-4">
                <p class="text-xs text-slate-500">{{ $label }}</p>
                <p class="text-lg font-semibold">{{ number_format($summary[$n]['plg']) }} Plg</p>
                <p class="text-sm text-slate-500">Rp {{ number_format($summary[$n]['rp'],0,',','.') }}</p>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg border p-4 mt-4">
        <p class="text-sm font-medium mb-1">Tabel Monitoring Saldo Lembar per Petugas</p>
        <p class="text-xs text-slate-400 mb-3">Klik angka pada kolom 2 Lembar / 3 Lembar untuk melihat rincian pelanggan</p>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-slate-500 border-b">
                <tr><th class="py-2 pr-3">Petugas</th><th class="py-2 pr-3">Posko</th><th class="py-2 pr-3">KDDK</th>
                    <th class="py-2 pr-3">1 Lembar</th><th class="py-2 pr-3">2 Lembar</th><th class="py-2 pr-3">3 Lembar</th></tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    <tr class="border-b">
                        <td class="py-2 pr-3 font-medium">{{ $row['petugas'] }}</td>
                        <td class="py-2 pr-3">{{ $row['posko'] }}</td>
                        <td class="py-2 pr-3">{{ $row['kddk'] }}</td>
                        <td class="py-2 pr-3">{{ $row['bucket'][1]['plg'] }} / Rp {{ number_format($row['bucket'][1]['rp'],0,',','.') }}</td>
                        <td class="py-2 pr-3">
                            @if ($row['bucket'][2]['plg'] > 0)
                                <button @click="buka('{{ $row['kddk'] }}', 2, '{{ $row['petugas'] }}')" class="text-blue-600 underline">
                                    {{ $row['bucket'][2]['plg'] }} / Rp {{ number_format($row['bucket'][2]['rp'],0,',','.') }}
                                </button>
                            @else - @endif
                        </td>
                        <td class="py-2 pr-3">
                            @if ($row['bucket'][3]['plg'] > 0)
                                <button @click="buka('{{ $row['kddk'] }}', 3, '{{ $row['petugas'] }}')" class="text-blue-600 underline">
                                    {{ $row['bucket'][3]['plg'] }} / Rp {{ number_format($row['bucket'][3]['rp'],0,',','.') }}
                                </button>
                            @else - @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    {{-- MODAL DRILL-DOWN --}}
    <div x-show="modalOpen" x-cloak class="fixed inset-0 bg-black/40 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full max-h-[80vh] overflow-y-auto p-5" @click.outside="modalOpen = false">
            <div class="flex justify-between items-center mb-3">
                <p class="font-semibold text-sm">Rincian Pelanggan Tunggakan — Petugas: <span x-text="petugasNama"></span> — Kategori: <span x-text="lembar"></span> Lembar</p>
                <button @click="modalOpen = false" class="text-slate-400">✕</button>
            </div>
            <template x-if="loading"><p class="text-sm text-slate-400">Memuat data...</p></template>
            <template x-for="p in pelangganList" :key="p.idpel">
                <div class="border-b py-2 text-sm">
                    <p class="font-medium" x-text="p.nama"></p>
                    <p class="text-xs text-slate-500" x-text="'IDPEL: ' + p.idpel + ' — ' + p.tarif + ' / ' + p.daya + ' VA'"></p>
                    <p class="text-xs text-slate-500" x-text="p.alamat"></p>
                    <ul class="text-xs mt-1 list-disc list-inside">
                        <template x-for="b in p.unpaid_bills" :key="b.thblrek">
                            <li x-text="'Rekening ' + b.thblrek + ' : Rp ' + Number(b.rp_tag).toLocaleString('id-ID')"></li>
                        </template>
                    </ul>
                    <p class="text-xs font-semibold mt-1" x-text="'Total Tunggakan: Rp ' + Number(p.total_rp_tagihan).toLocaleString('id-ID')"></p>
                </div>
            </template>
            <template x-if="!loading && pelangganList.length === 0"><p class="text-sm text-slate-400">Tidak ada rincian.</p></template>
        </div>
    </div>
</div>

<script>
function lembarModal() {
    return {
        modalOpen: false,
        loading: false,
        petugasNama: '',
        lembar: null,
        pelangganList: [],
        async buka(kddk, lembar, petugasNama) {
            this.modalOpen = true;
            this.loading = true;
            this.petugasNama = petugasNama;
            this.lembar = lembar;
            const res = await fetch(`{{ route('dashboard.lembar.detail') }}?kddk=${kddk}&lembar=${lembar}`);
            const data = await res.json();
            this.pelangganList = data.pelanggan;
            this.loading = false;
        },
    };
}
</script>
@endsection
