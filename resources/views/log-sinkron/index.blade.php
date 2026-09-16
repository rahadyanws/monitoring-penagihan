@extends('layouts.app')
@section('title', 'Log Sinkron')

@section('content')
<div x-data="logModal()">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">
                <span>Aplikasi</span><span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span>Data Transaksi</span><span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-[#0066cc]">Log Sinkronisasi</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Log Sinkronisasi &amp; Riwayat Upload</h1>
            <p class="text-xs text-gray-500 mt-1 max-w-2xl">Audit jejak pemrosesan berkas oleh background queue worker secara real-time.</p>
        </div>
        <div class="flex items-center gap-2 px-3.5 py-2 rounded-full bg-white border border-emerald-200 shadow-sm self-start lg:self-center">
            <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            <span class="text-[11px] font-bold tracking-wider text-emerald-800 uppercase">ENGINE STATUS: READY</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div class="p-5 rounded-2xl bg-white border border-[#e0e0e0] shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Worker Daemon</span>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $statusWorker }}</div>
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#e0e0e0] shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Antrean Berkas</span>
            <div class="mt-2 text-xl font-bold text-gray-900">{{ $antrean }} Berkas</div>
            @if ($sedangDiproses)
                <div class="mt-1 text-xs text-gray-500 truncate">{{ $sedangDiproses->file_name }} (#{{ $sedangDiproses->id_upload }})</div>
            @endif
        </div>
        <div class="p-5 rounded-2xl bg-white border border-[#e0e0e0] shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Throughput Parsing</span>
            <div class="mt-2 text-xl font-bold text-gray-900">2.450 baris/detik</div>
        </div>
    </div>

    <form method="GET" class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm mt-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Jenis Data</label>
                <select name="file_type" onchange="this.form.submit()" class="w-full h-10 pl-3.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#0066cc]">
                    <option value="">Semua Jenis Data</option>
                    <option value="MASTER_DATA" @selected(request('file_type')==='MASTER_DATA')>Master DKRP</option>
                    <option value="DAILY_TRANSACTION" @selected(request('file_type')==='DAILY_TRANSACTION')>Transaksi Harian</option>
                    <option value="TARGET_MONITORING" @selected(request('file_type')==='TARGET_MONITORING')>Target Saldo</option>
                </select>
            </div>
            <div class="space-y-1.5">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Status Pemrosesan</label>
                <select name="status" onchange="this.form.submit()" class="w-full h-10 pl-3.5 pr-9 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#0066cc]">
                    <option value="">Semua Status</option>
                    @foreach (['COMPLETED','PROCESSING','FAILED','PENDING'] as $s)
                        <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="space-y-1.5 sm:col-span-2 lg:col-span-1">
                <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider">Cari Nama Berkas</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="e.g. MASTER_DATA..." class="w-full h-10 px-3.5 bg-gray-50 border border-gray-200 rounded-xl text-xs font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-[#0066cc]">
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="flex-1 h-10 rounded-xl bg-[#0066cc] hover:bg-[#0052a3] text-white font-semibold text-xs flex items-center justify-center gap-1.5">
                    <span class="material-symbols-outlined text-[17px]">filter_alt</span><span>Terapkan Filter</span>
                </button>
                <a href="{{ route('log-sinkron.index') }}" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 border border-gray-200 text-gray-600 flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">restart_alt</span></a>
            </div>
        </div>
    </form>

    <div class="rounded-2xl bg-white border border-[#e0e0e0] shadow-sm overflow-hidden mt-4">
        <div class="p-4 border-b border-gray-100 flex items-center gap-2">
            <h2 class="font-bold text-sm text-gray-900">Daftar Aktivitas Sinkronisasi Berkas</h2>
            <span class="px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 font-bold text-[10px] border border-gray-200">{{ $logs->total() }} BERKAS</span>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-gray-700">
            <thead class="bg-[#f8f9fa] text-gray-500 font-bold text-[11px] uppercase tracking-wider border-b border-gray-200">
                <tr>
                    <th class="py-3 px-4">ID</th><th class="py-3 px-4">Waktu</th><th class="py-3 px-4">Nama Berkas</th>
                    <th class="py-3 px-4">Jenis Data</th><th class="py-3 px-4">Baris Diproses</th><th class="py-3 px-4">Status</th><th class="py-3 px-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($logs as $log)
                    @php
                        $statusStyle = [
                            'COMPLETED' => ['bg-emerald-50 border-emerald-200 text-emerald-700', 'check_circle'],
                            'PROCESSING' => ['bg-blue-50 border-blue-200 text-[#0066cc] animate-pulse', 'autorenew'],
                            'FAILED' => ['bg-rose-100 border-rose-200 text-rose-700', 'cancel'],
                            'PENDING' => ['bg-gray-100 border-gray-200 text-gray-600', 'schedule'],
                        ][$log->status] ?? ['bg-gray-100 text-gray-600', 'help'];
                        $iconType = [
                            'MASTER_DATA' => ['bg-amber-50 border-amber-100 text-amber-600', 'MASTER DKRP', 'bg-amber-50 border-amber-200 text-amber-700'],
                            'DAILY_TRANSACTION' => ['bg-emerald-50 border-emerald-100 text-emerald-600', 'TRANSAKSI', 'bg-gray-100 border-gray-200 text-gray-700'],
                            'TARGET_MONITORING' => ['bg-emerald-50 border-emerald-100 text-emerald-600', 'TARGET SALDO', 'bg-blue-50 border-blue-200 text-[#0066cc]'],
                        ][$log->file_type];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors {{ $log->status === 'FAILED' ? 'bg-rose-50/40' : ($log->status === 'PROCESSING' ? 'bg-blue-50/30' : '') }}">
                        <td class="py-3.5 px-4 font-mono font-bold text-gray-600">#{{ $log->id_upload }}</td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex flex-col"><span class="font-semibold text-gray-900">{{ $log->created_at->format('d/m/Y') }}</span><span class="text-[11px] text-gray-400 font-mono">{{ $log->created_at->format('H:i') }} WIB</span></div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg {{ $iconType[0] }} border flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-[18px]">table_chart</span></div>
                                <div class="flex flex-col"><span class="font-semibold text-gray-900 truncate max-w-xs">{{ $log->file_name }}</span><span class="text-[11px] text-gray-400">{{ number_format($log->file_size_bytes/1024,0) }} KB</span></div>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap"><span class="px-2 py-0.5 rounded-md border text-[10px] font-bold uppercase tracking-wider {{ $iconType[2] }}">{{ $iconType[1] }}</span></td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="font-mono font-semibold text-gray-800">{{ number_format($log->success_rows) }} / {{ number_format($log->total_rows) }} baris</span>
                                <span class="text-[11px] {{ $log->status === 'FAILED' ? 'text-rose-500' : 'text-emerald-600' }} font-medium">{{ $log->status === 'FAILED' ? ($log->error_message ? 'Gagal parsing' : '-') : $log->progressPercent().'% committed' }}</span>
                            </div>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-[10px] font-bold uppercase {{ $statusStyle[0] }}"><span class="material-symbols-outlined text-[13px]">{{ $statusStyle[1] }}</span><span>{{ $log->status }}</span></span></td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <button @click="buka({{ $log->id_upload }})" class="inline-flex items-center gap-1 text-[#0066cc] font-semibold hover:underline">
                                <span>Detail Log</span><span class="material-symbols-outlined text-[15px]">chevron_right</span>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-6 text-center text-gray-400">Belum ada riwayat upload.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>

    <!-- Modal Detail Log -->
    <div x-show="modalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden" @click.outside="modalOpen=false">
            <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between bg-gray-50/70">
                <span class="px-2.5 py-1 rounded-md bg-gray-200/80 font-mono text-xs font-bold text-gray-800 uppercase">AUDIT LOG</span>
                <button @click="modalOpen=false" class="w-8 h-8 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center"><span class="material-symbols-outlined text-[18px]">close</span></button>
            </div>
            <div class="p-6 space-y-4 text-xs" x-show="!loading">
                <h3 class="text-base font-bold text-gray-900" x-text="'RINCIAN LOG #' + detail.id_upload + ' — ' + detail.status"></h3>
                <div class="p-4 rounded-xl bg-gray-50 border border-gray-200 space-y-2">
                    <div class="flex justify-between"><span class="text-gray-500">Nama Berkas:</span><span class="font-semibold font-mono" x-text="detail.file_name"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Diunggah Oleh:</span><span class="font-semibold" x-text="detail.uploaded_by"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Waktu Mulai:</span><span class="font-mono" x-text="detail.created_at"></span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Selesai:</span><span class="font-mono" x-text="detail.completed_at || '-'"></span></div>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-gray-50 border border-gray-200"><span class="block text-[10px] font-bold text-gray-400 uppercase">Total</span><span class="text-sm font-bold text-gray-900" x-text="detail.total_rows"></span></div>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200"><span class="block text-[10px] font-bold text-emerald-500 uppercase">Sukses</span><span class="text-sm font-bold text-emerald-700" x-text="detail.success_rows"></span></div>
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200"><span class="block text-[10px] font-bold text-rose-500 uppercase">Gagal</span><span class="text-sm font-bold text-rose-700" x-text="detail.failed_rows"></span></div>
                </div>
                <template x-if="detail.error_message">
                    <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700" x-text="detail.error_message"></div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
function logModal() {
    return {
        modalOpen: false, loading: false, detail: {},
        async buka(id) {
            this.modalOpen = true; this.loading = true;
            const res = await fetch(`/log-sinkron/${id}`);
            this.detail = await res.json();
            this.loading = false;
        },
    };
}
</script>
@endsection
