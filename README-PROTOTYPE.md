# PROTOTYPE PRESENTASI — Sistem Monitoring Penagihan PLN UP3 Indramayu

Paket ini berisi **kode drop-in** (migration, model, seeder, controller, view) untuk 6 dashboard
di `PRD.md` / `DESIGN.md`, memakai **data dummy/fiktif** — belum lewat pipeline upload Excel asli
(`ProcessMasterDataJob`, dsb di `AGENTS.md`). Cocok untuk didemokan ke atasan sebelum data real siap.

Struktur folder di paket ini mengikuti struktur project Laravel standar — tinggal ditimpa ke project baru.

## 1. Siapkan project Laravel baru

```bash
composer create-project laravel/laravel monitoring-penagihan
cd monitoring-penagihan
```

## 2. Salin isi paket prototype ini ke dalam project

Salin folder berikut dari paket ini (menimpa/menambah) ke root project Laravel Anda:

```
app/Models/*.php                     -> app/Models/
app/Http/Controllers/*.php           -> app/Http/Controllers/
database/migrations/*.php            -> database/migrations/
database/seeders/*.php                -> database/seeders/
resources/views/layouts/*.blade.php   -> resources/views/layouts/
resources/views/dashboard/*.blade.php -> resources/views/dashboard/
resources/views/upload-excel/*.blade.php -> resources/views/upload-excel/
resources/views/log-sinkron/*.blade.php  -> resources/views/log-sinkron/
routes/web.php                        -> routes/web.php  (TIMPA file bawaan)
```

## 3. Konfigurasi .env & database

Ikuti `SETUP.md` (poin 2, langkah 1-4) untuk konfigurasi `.env` (PostgreSQL/MySQL).
Untuk prototype presentasi, **timezone & locale Indonesia** sebaiknya diset di `config/app.php`:

```php
'timezone' => 'Asia/Jakarta',
'locale' => 'id',
'faker_locale' => 'id_ID',
```

## 4. Migrasi & seed data dummy

```bash
php artisan migrate:fresh --seed
```

Ini akan membuat:
- 1 ULP (Indramayu Kota), 5 Posko (Kota 1, Kota 2, Kota 3, Lohbener, Arahan)
- ~15 Petugas (3 di antaranya persis sesuai contoh di PRD.md: SEFIANA/ACA, DARKAWI/ACB, AGUS/ACD)
- ± 1.000+ pelanggan dummy dengan sebaran Kogol & Daya realistis
- 14 pelanggan Dabes (4 di antaranya persis sesuai contoh di API_SPEC.md/DESIGN.md, termasuk
  riwayat 12 bulan pembayaran, agar tampilan tren & alert cocok dengan wireframe)
- Tagihan bulan berjalan + sebagian sengaja dibuat nunggak 2/3 lembar
- Transaksi harian bulan berjalan & bulan lalu dengan sebaran tanggal bayar per Kogol
- 4 contoh riwayat di tabel `upload_log` (persis wireframe Riwayat & Log Sinkronisasi.html:
  1 COMPLETED Target, 1 COMPLETED Transaksi, 1 PROCESSING Master DKRP, 1 FAILED dengan pesan error)

> Seeder ini **memakai tanggal hari ini** (`Carbon::now()`) sebagai "bulan berjalan", jadi datanya
> akan selalu relevan kapan pun `--seed` dijalankan ulang — cocok untuk demo berkali-kali.

## 5. Jalankan

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000` — otomatis redirect ke Dashboard Posko & Petugas. Menu sidebar berisi
ke-6 dashboard: Posko & Petugas, Tagihan Dabes, Kode Kelompok, Bayar Tgl 21+, Saldo Lembar.

Tidak perlu `npm install` / `npm run dev` untuk prototype ini — Tailwind, Alpine.js, dan Chart.js
dimuat lewat CDN langsung di `layouts/app.blade.php` supaya presentasi bisa langsung jalan.
**Untuk versi produksi**, ganti ke Tailwind v4 terkompilasi sesuai `SETUP.md`.

## 6. Yang SUDAH bisa didemokan

- ✅ Semua 6 dashboard, dengan grafik (Chart.js) & tabel sesuai wireframe `DESIGN.md`
- ✅ **Halaman Upload Data** (`/upload-excel`) — form 3-step (jenis data → periode/ULP → dropzone),
  upload beneran menyimpan file ke `storage/app/imports` dan mencatat baris baru di `upload_log`
- ✅ **Halaman Log Sinkron** (`/log-sinkron`) — filter jenis data/status/pencarian, modal detail log
  audit (termasuk pesan error untuk berkas FAILED), status PROCESSING/COMPLETED/FAILED berwarna
- ✅ **Navigasi & layout seragam di semua 7 halaman** — sidebar & topbar diambil persis dari struktur
  referensi `Pola_Kode_Kelompok.html` yang diberikan client (grup menu Dashboard / Data Transaksi /
  Pengaturan, warna brand `#0066cc`, kartu `rounded-2xl`, ikon Material Symbols, pill filter/badge)
- ✅ Filter Posko pada Dashboard Posko & Petugas
- ✅ Banner early-warning + tren 12 bulan (klik "Lihat tren") pada Dashboard Dabes
- ✅ Export CSV pada Dashboard Bayar Tgl 21+ (placeholder untuk .xlsx asli — lihat komentar di
  `DashboardBayarTgl21Controller::export()`)
- ✅ Modal drill-down interaktif (klik angka 2/3 Lembar) via AJAX + Alpine.js

## 7. Yang BELUM ada di prototype ini (sengaja, di luar scope presentasi)

- ❌ Parsing/validasi baris Excel ASLI (`rap2hpoutre/fast-excel`, `ProcessMasterDataJob` dkk di
  `AGENTS.md`) — file yang diupload di halaman Upload Data langsung ditandai COMPLETED dengan
  estimasi jumlah baris dari ukuran file, BUKAN hasil parsing sungguhan
- ❌ Redis queue worker sungguhan — status PROCESSING di seed data bersifat statis (tidak otomatis
  lanjut ke COMPLETED), murni untuk menunjukkan tampilan visual status tersebut
- ❌ RBAC / role Koordinator Posko / Petugas (baru 1 user generik)
- ❌ Export `.xlsx` asli (masih CSV) — tinggal ganti ke `maatwebsite/excel` saat produksi
- ❌ Materialized view / caching agregasi (query masih langsung ke tabel transaksional)
- ❌ Halaman "Ringkasan ULP", "Mapping KDDK", "Master Petugas" — muncul di sidebar (mengikuti
  struktur navigasi acuan) tapi belum ada halamannya (link nonaktif dengan tooltip "Segera hadir")

Semua item di atas sudah direncanakan di `ROADMAP.md` Milestone 1-4 — prototype ini murni untuk
mempercepat presentasi visual ke atasan, strukturnya sudah align dengan skema final di
`DATABASE_SCHEMA.md` (+ tabel baru `upload_log`) sehingga tidak perlu dibongkar ulang saat lanjut
ke pipeline produksi.

## 8. Open items yang masih perlu dikonfirmasi ke client (lihat juga PRD.md)

- Jumlah & mapping definitif Kode Kelompok (Kogol) — seeder ini asumsi 0/1/2/3/9.
- Apakah "Posko" & "Petugas" tetap 1 halaman (seperti prototype ini) atau dipisah jadi 2 menu.

## 9. Riwayat revisi

- **v0.1**: Prototype awal, styling Tailwind generik buatan sendiri.
- **v0.2** (revisi ini): Restyle total mengikuti 7 file HTML mockup yang diberikan client
  (Pola_Kode_Kelompok, Posko_Petugas, Tagihan_Dabes, Saldo_Tunggakan_Lembar,
  Pelanggan_Bayar_Tgl_21, Upload_Excel, Riwayat_Log_Sinkronisasi) — struktur navigasi & layout
  global diseragamkan mengikuti `Pola_Kode_Kelompok.html` sesuai instruksi. Ditambahkan 2 halaman
  baru (Upload Data, Log Sinkron) beserta tabel `upload_log`, model, controller, dan seeder-nya.
