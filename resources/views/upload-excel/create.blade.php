@extends('layouts.app')
@section('title', 'Upload Data')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200/80">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
        </span>
        <span class="text-[11px] font-semibold text-emerald-800 tracking-wide uppercase">Engine Status: Ready • Antrean Pemrosesan Normal</span>
    </div>
</div>

<div>
    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Upload Data Rekening &amp; Transaksi Excel</h1>
    <p class="text-sm text-gray-500 max-w-3xl mt-1">Import berkas master tagihan (DKRP), transaksi pelunasan harian, dan monitoring target saldo petugas ke sistem.</p>
</div>

@if ($errors->any())
    <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('upload-excel.store') }}" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
@csrf
    <div class="lg:col-span-8 flex flex-col rounded-2xl bg-white border border-[#e0e0e0] shadow-sm p-6 space-y-6">
        <!-- STEP 1 -->
        <div class="space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#0066cc] text-white flex items-center justify-center text-[11px] font-bold">1</span>
                    PILIH JENIS DATA REKENING
                </span>
                <span class="text-[11px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200/60">Wajib Dipilih</span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                @foreach ([
                    ['MASTER_DATA', 'database', 'DKRP 1L/2L/3L', 'Master Data Bulanan', 'Data induk tagihan, piutang rekening, dan saldo per lembar awal.'],
                    ['DAILY_TRANSACTION', 'receipt_long', 'Harian', 'Transaksi Pembayaran', 'Pelunasan real-time posko, loket perbankan, dan tgl 21+.'],
                    ['TARGET_MONITORING', 'assignment_turned_in', 'KDDK', 'Monitoring Target Saldo', 'Penetapan pagu sisa tagihan, rute baca, & posko petugas.'],
                ] as $i => [$val, $icon, $tag, $title, $desc])
                    <label class="group relative flex flex-col p-4 rounded-xl cursor-pointer border-2 border-gray-200 bg-gray-50/60 hover:bg-blue-50/40 hover:border-[#0066cc] transition-all has-[:checked]:border-[#0066cc] has-[:checked]:bg-blue-50/40">
                        <input type="radio" name="file_type" value="{{ $val }}" class="sr-only" @checked($i === 0) required>
                        <div class="flex items-center justify-between mb-2">
                            <div class="w-8 h-8 rounded-lg bg-[#0066cc] text-white flex items-center justify-center shadow-sm"><span class="material-symbols-outlined text-[18px]">{{ $icon }}</span></div>
                            <span class="px-2 py-0.5 rounded-full bg-blue-100 text-[#0066cc] font-bold text-[10px] uppercase tracking-wide">{{ $tag }}</span>
                        </div>
                        <span class="text-xs font-bold text-gray-900">{{ $title }}</span>
                        <span class="text-[11px] text-gray-500 mt-1 leading-relaxed">{{ $desc }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- STEP 2 -->
        <div class="space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#0066cc] text-white flex items-center justify-center text-[11px] font-bold">2</span>
                    PARAMETER PENUGASAN &amp; LINGKUP UNIT
                </span>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-700">Periode Bulan Rekening (THBLREK)</label>
                    <div class="relative">
                        <select name="thblrek" class="w-full h-11 pl-3.5 pr-10 bg-gray-50 hover:bg-gray-100/70 border border-gray-200 text-xs font-medium text-gray-800 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-[#0066cc] cursor-pointer">
                            @for ($m = 0; $m < 4; $m++)
                                @php $p = now()->copy()->subMonthsNoOverflow($m); @endphp
                                <option value="{{ $p->format('Ym') }}">{{ $p->format('Y-m') }} ({{ $p->translatedFormat('F Y') }}){{ $m === 0 ? ' - Bulan Berjalan' : '' }}</option>
                            @endfor
                        </select>
                        <span class="material-symbols-outlined text-gray-400 pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px]">expand_more</span>
                    </div>
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-700">Unit Layanan Pelanggan (ULP)</label>
                    <div class="relative">
                        <select name="id_ulp" class="w-full h-11 pl-3.5 pr-10 bg-gray-50 hover:bg-gray-100/70 border border-gray-200 text-xs font-medium text-gray-800 rounded-xl appearance-none focus:outline-none focus:ring-2 focus:ring-[#0066cc] cursor-pointer">
                            <option value="53403" selected>53403 - ULP Indramayu Kota</option>
                        </select>
                        <span class="material-symbols-outlined text-gray-400 pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[20px]">expand_more</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 3 -->
        <div class="space-y-3">
            <div class="flex items-center justify-between pb-1 border-b border-gray-100">
                <span class="text-xs font-bold uppercase tracking-wider text-gray-700 flex items-center gap-2">
                    <span class="w-5 h-5 rounded-full bg-[#0066cc] text-white flex items-center justify-center text-[11px] font-bold">3</span>
                    PILIH BERKAS EXCEL
                </span>
            </div>
            <label class="group relative rounded-2xl border-2 border-dashed border-gray-300 hover:border-[#0066cc] bg-gray-50/70 hover:bg-blue-50/20 transition-all p-8 text-center cursor-pointer flex flex-col items-center justify-center">
                <input accept=".xls,.xlsx" name="file" required class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10" type="file"
                       onchange="document.getElementById('fileLabel').textContent = this.files[0] ? this.files[0].name : 'Belum ada file dipilih'">
                <div class="w-14 h-14 rounded-2xl bg-white border border-gray-200 shadow-sm flex items-center justify-center mb-3 group-hover:scale-105 group-hover:border-[#0066cc] transition-all">
                    <span class="material-symbols-outlined text-[32px] text-[#0066cc]">cloud_upload</span>
                </div>
                <h3 class="text-sm font-bold text-gray-800 mb-1">Tarik dan lepas file Excel (.xls, .xlsx) di sini atau <span class="text-[#0066cc] underline font-semibold">Klik untuk Telusuri</span></h3>
                <p class="text-xs text-gray-400 max-w-md">Mendukung .xls &amp; .xlsx hingga 50 MB per pengunggahan.</p>
                <p id="fileLabel" class="mt-3 text-xs font-semibold text-gray-600">Belum ada file dipilih</p>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('upload-excel.create') }}" class="px-4 py-2 rounded-full border border-gray-200 bg-white hover:bg-gray-50 text-gray-600 font-semibold text-xs transition-colors">Reset Formulir</a>
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-full bg-[#0066cc] hover:bg-[#0052a3] text-white text-xs font-bold shadow-md transition-all">
                <span class="material-symbols-outlined text-[18px]">publish</span><span>Upload &amp; Proses Antrean</span>
            </button>
        </div>
    </div>

    <div class="lg:col-span-4 flex flex-col gap-6">
        <div class="rounded-2xl bg-gradient-to-br from-gray-900 to-gray-800 text-white p-6 shadow-md relative overflow-hidden">
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-gray-400">System Throughput</span>
                    <span class="px-2 py-0.5 rounded-full bg-blue-500/20 text-blue-400 border border-blue-400/30 font-semibold text-[10px]">Realtime</span>
                </div>
                <div class="flex items-baseline gap-2 pt-1">
                    <h4 class="text-3xl font-extrabold tracking-tight text-white">{{ number_format($throughputBarisPerDetik) }}</h4>
                    <span class="text-xs font-medium text-gray-300">baris / detik</span>
                </div>
            </div>
            <div class="space-y-2 mt-6 pt-4 border-t border-gray-700/60">
                <div class="flex justify-between text-xs text-gray-300"><span>Kapasitas Antrean Pekerja</span><span class="font-bold text-white">{{ $antreanTerpakaiPersen }}% Terpakai</span></div>
                <div class="w-full h-2 rounded-full bg-gray-700/60 overflow-hidden"><div class="h-full bg-gradient-to-r from-blue-400 to-[#0066cc] rounded-full" style="width: {{ $antreanTerpakaiPersen }}%;"></div></div>
            </div>
        </div>
        <div class="rounded-2xl bg-white border border-[#e0e0e0] p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-gray-800 flex items-center gap-2 pb-2 border-b border-gray-100">
                <span class="material-symbols-outlined text-[18px] text-[#0066cc]">checklist</span>Aturan Unggah Berkas
            </h3>
            <div class="space-y-2.5 text-xs text-gray-600">
                <div class="flex items-start gap-2"><span class="material-symbols-outlined text-[15px] text-emerald-600 mt-0.5">check_circle</span><span>Kolom <strong>IDPEL</strong> harus 12 digit numerik.</span></div>
                <div class="flex items-start gap-2"><span class="material-symbols-outlined text-[15px] text-emerald-600 mt-0.5">check_circle</span><span>File DKRP wajib memuat kolom <strong>KDDK</strong> valid.</span></div>
                <div class="flex items-start gap-2"><span class="material-symbols-outlined text-[15px] text-emerald-600 mt-0.5">check_circle</span><span>Ukuran maksimum <strong>50 MB</strong> per berkas.</span></div>
            </div>
        </div>
    </div>
</form>
@endsection
