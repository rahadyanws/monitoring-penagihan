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
- ✅ Filter Posko pada Dashboard Posko & Petugas
- ✅ Banner early-warning + tren 12 bulan (klik "Lihat tren") pada Dashboard Dabes
- ✅ Export CSV pada Dashboard Bayar Tgl 21+ (placeholder untuk .xlsx asli — lihat komentar di
  `DashboardBayarTgl21Controller::export()`)
- ✅ Modal drill-down interaktif (klik angka 2/3 Lembar) via AJAX + Alpine.js

## 7. Yang BELUM ada di prototype ini (sengaja, di luar scope presentasi)

- ❌ Upload & proses Excel asli (`ProcessMasterDataJob`, queue worker, `rap2hpoutre/fast-excel`)
- ❌ RBAC / role Koordinator Posko / Petugas (baru 1 user generik)
- ❌ Export `.xlsx` asli (masih CSV) — tinggal ganti ke `maatwebsite/excel` saat produksi
- ❌ Materialized view / caching agregasi (query masih langsung ke tabel transaksional)

Semua item di atas sudah direncanakan di `ROADMAP.md` Milestone 1-4 — prototype ini murni untuk
mempercepat presentasi visual ke atasan, strukturnya sudah align dengan skema final di
`DATABASE_SCHEMA.md` sehingga tidak perlu dibongkar ulang saat lanjut ke pipeline produksi.

## 8. Open items yang masih perlu dikonfirmasi ke client (lihat juga PRD.md)

- Jumlah & mapping definitif Kode Kelompok (Kogol) — seeder ini asumsi 0/1/2/3/9.
- Apakah "Posko" & "Petugas" tetap 1 halaman (seperti prototype ini) atau dipisah jadi 2 menu.
