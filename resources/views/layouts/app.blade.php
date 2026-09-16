<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>@yield('title', 'Dashboard') - PLN UP3 Indramayu</title>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    {{-- PROTOTYPE: Tailwind, Alpine.js & Chart.js via CDN supaya bisa langsung jalan tanpa `npm run build`.
         Untuk versi produksi, ganti ke Tailwind v4 terkompilasi sesuai SETUP.md. --}}
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: { sans: ['Inter', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'] },
            colors: { brand: { blue: '#0066cc', darkBlue: '#004e9f', lightBlue: '#eef6ff' } }
          }
        }
      }
    </script>
    <style>
      body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background-color: #f5f5f7; color: #1d1d1f; }
      ::-webkit-scrollbar { width: 6px; height: 6px; }
      ::-webkit-scrollbar-track { background: transparent; }
      ::-webkit-scrollbar-thumb { background: #d2d2d7; border-radius: 9999px; }
      [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen bg-[#f5f5f7] text-[#1d1d1f] antialiased flex">

{{-- Sidebar & header: struktur diambil persis dari referensi "Pola Kode Kelompok.html"
     supaya seluruh halaman seragam (lihat catatan revisi di README-PROTOTYPE.md §9). --}}
<aside class="fixed left-0 top-0 bottom-0 w-64 bg-white border-r border-[#e0e0e0] z-50 flex flex-col justify-between select-none">
    <div class="flex flex-col">
        <div class="h-16 px-5 border-b border-[#f0f0f0] flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg bg-[#0066cc] flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5 text-yellow-300 fill-current" viewBox="0 0 24 24"><path d="M13 2L3 14h8l-2 8 10-12h-8l2-8z"></path></svg>
            </div>
            <div class="flex flex-col leading-tight">
                <span class="font-bold text-sm tracking-tight text-gray-900">PLN UP3 INDRAMAYU</span>
                <span class="text-[11px] text-gray-500 font-medium">Monitoring Penagihan</span>
            </div>
        </div>
        <nav class="p-3 space-y-6 overflow-y-auto">
            @php
                $navClass = fn ($active) => $active
                    ? 'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-semibold bg-[#0066cc] text-white shadow-sm transition-all'
                    : 'flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition-colors';
            @endphp
            <div class="space-y-1">
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">Dashboard</div>
                <a class="{{ $navClass(false) }}" href="#" title="Segera hadir - di luar scope prototype ini">
                    <span class="material-symbols-outlined text-[19px]">analytics</span><span>Ringkasan ULP</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('dashboard.posko-petugas')) }}" href="{{ route('dashboard.posko-petugas') }}">
                    <span class="material-symbols-outlined text-[19px]">badge</span><span>Posko &amp; Petugas</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('dashboard.dabes')) }}" href="{{ route('dashboard.dabes') }}">
                    <span class="material-symbols-outlined text-[19px]">receipt_long</span><span>Tagihan Dabes</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('dashboard.kode-kelompok')) }}" href="{{ route('dashboard.kode-kelompok') }}">
                    <span class="material-symbols-outlined text-[19px]">category</span><span>Kode Kelompok</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('dashboard.bayar-tgl21*')) }}" href="{{ route('dashboard.bayar-tgl21') }}">
                    <span class="material-symbols-outlined text-[19px]">event_available</span><span>Bayar Tgl 21+</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('dashboard.lembar*')) }}" href="{{ route('dashboard.lembar') }}">
                    <span class="material-symbols-outlined text-[19px]">account_balance_wallet</span><span>Saldo Lembar</span>
                </a>
            </div>
            <div class="space-y-1">
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">Data Transaksi</div>
                <a class="{{ $navClass(request()->routeIs('upload-excel.*')) }}" href="{{ route('upload-excel.create') }}">
                    <span class="material-symbols-outlined text-[19px]">cloud_upload</span><span>Upload Data</span>
                </a>
                <a class="{{ $navClass(request()->routeIs('log-sinkron.*')) }}" href="{{ route('log-sinkron.index') }}">
                    <span class="material-symbols-outlined text-[19px]">sync</span><span>Log Sinkron</span>
                </a>
            </div>
            <div class="space-y-1">
                <div class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">Pengaturan</div>
                <a class="{{ $navClass(false) }}" href="#" title="Segera hadir - di luar scope prototype ini">
                    <span class="material-symbols-outlined text-[19px]">schema</span><span>Mapping KDDK</span>
                </a>
                <a class="{{ $navClass(false) }}" href="#" title="Segera hadir - di luar scope prototype ini">
                    <span class="material-symbols-outlined text-[19px]">manage_accounts</span><span>Master Petugas</span>
                </a>
            </div>
        </nav>
    </div>
    <div class="p-4 border-t border-[#f0f0f0]">
        <div class="bg-gray-50 border border-[#e5e7eb] rounded-xl p-3 flex items-center justify-between">
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-800">Prototype v0.1</span>
                <span class="text-[11px] text-gray-500">UP3 Indramayu</span>
            </div>
            <span class="material-symbols-outlined text-[#0066cc] text-[18px]">verified</span>
        </div>
    </div>
</aside>

<div class="flex-1 ml-64 min-w-0">
    <header class="sticky top-0 z-40 h-16 bg-white/90 backdrop-blur-md border-b border-[#e0e0e0] px-8 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs font-medium text-gray-500">
            <span class="text-gray-400 uppercase tracking-wider text-[11px]">Aplikasi</span>
            <span>/</span>
            <span class="font-semibold text-gray-900">SISTEM MONITORING PENAGIHAN - UP3 INDRAMAYU</span>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3.5 py-1.5 rounded-full bg-gray-100 text-gray-700 text-xs font-medium flex items-center gap-1.5 border border-gray-200">
                <span class="material-symbols-outlined text-[16px] text-gray-500">calendar_month</span>
                <span>Periode: {{ $periode ?? now()->translatedFormat('F Y') }}</span>
            </div>
            <button aria-label="Notifikasi" class="w-9 h-9 rounded-full flex items-center justify-center hover:bg-gray-100 text-gray-600 relative transition-colors" type="button">
                <span class="material-symbols-outlined text-[20px]">notifications</span>
                <span class="absolute top-2 right-2 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white"></span>
            </button>
            <div class="flex items-center gap-2 pl-1 pr-3 py-1 rounded-full bg-gray-50 border border-gray-200">
                <div class="w-7 h-7 rounded-full bg-[#0066cc] text-white flex items-center justify-center font-bold text-xs">AP</div>
                <div class="flex flex-col text-left leading-tight">
                    <span class="text-xs font-semibold text-gray-900">Super Admin</span>
                    <span class="text-[10px] text-gray-500">Prototype</span>
                </div>
            </div>
        </div>
    </header>

    <main class="p-8 max-w-7xl mx-auto space-y-6">
        @if (session('success'))
            <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm px-4 py-3 flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        @yield('content')
    </main>
</div>

</body>
</html>
