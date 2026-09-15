<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Dashboard') - Monitoring Penagihan PLN UP3 Indramayu</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- PROTOTYPE: Tailwind & Alpine via CDN supaya bisa langsung jalan tanpa `npm run build`.
         Untuk versi produksi, ganti ke Tailwind v4 terkompilasi sesuai SETUP.md. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-100 text-slate-800" x-data="{ sidebarOpen: true }">

<div class="flex min-h-screen">
    {{-- SIDEBAR --}}
    <aside class="bg-slate-900 text-slate-200 w-64 shrink-0" x-show="sidebarOpen" x-cloak>
        <div class="px-4 py-5 border-b border-slate-700">
            <p class="text-sm font-semibold leading-tight text-white">PLN UP3 INDRAMAYU</p>
            <p class="text-xs text-slate-400">Monitoring Penagihan</p>
        </div>
        <nav class="p-3 space-y-4 text-sm">
            <div>
                <p class="px-2 mb-1 text-[11px] font-semibold text-slate-500">MONITORING</p>
                @php
                    $menu = [
                        'dashboard.posko-petugas' => 'Posko & Petugas',
                        'dashboard.dabes' => 'Tagihan Dabes',
                        'dashboard.kode-kelompok' => 'Kode Kelompok',
                        'dashboard.bayar-tgl21' => 'Bayar Tgl 21+',
                        'dashboard.lembar' => 'Saldo Lembar',
                    ];
                @endphp
                @foreach ($menu as $route => $label)
                    <a href="{{ route($route) }}"
                       class="block px-2 py-1.5 rounded-md {{ request()->routeIs($route) ? 'bg-blue-600 text-white' : 'text-slate-300 hover:bg-slate-800' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
            <div>
                <p class="px-2 mb-1 text-[11px] font-semibold text-slate-500">DATA & IMPORT</p>
                <span class="block px-2 py-1.5 text-slate-500">Upload Excel <em class="text-[10px]">(belum di-prototype)</em></span>
            </div>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        {{-- TOPBAR --}}
        <header class="bg-white border-b px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded hover:bg-slate-100">☰</button>
                <div>
                    <p class="text-xs text-slate-400">Dashboard</p>
                    <h1 class="text-base font-semibold text-slate-800">@yield('title')</h1>
                </div>
            </div>
            <div class="text-xs text-slate-500 text-right">
                <p>Periode: {{ $periode ?? now()->translatedFormat('F Y') }}</p>
                <p>User: Super Admin (Prototype)</p>
            </div>
        </header>

        <main class="p-4 space-y-4">
            @yield('content')
        </main>
    </div>
</div>

</body>
</html>
