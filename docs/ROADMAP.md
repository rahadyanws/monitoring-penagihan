# DEVELOPMENT ROADMAP
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

---

## Milestone 1: Arsitektur Dasar & Pipeline Ingestion Data (Minggu 1 - 2)
- [x] Analisis skema atribut Master Data (89 kolom), Transaksi Harian (18 kolom), dan Target Saldo.
- [ ] Inisialisasi project Laravel 13 dengan Tailwind CSS v4 dan Alpine.js/Livewire.
- [ ] Implementasi skema DDL Migration & Indexing database relasional (PostgreSQL/MySQL).
- [ ] Implementasi Service Ingestion Streaming Excel (`rap2hpoutre/fast-excel`) untuk bulk import.
- [ ] Konfigurasi antrean background worker (Laravel Queue / Redis) dan audit log upload.
- [ ] Implementasi logika rekonsiliasi otomatis (`DailyTransactionProcessed` -> update status lunas).

## Milestone 2: Dashboard Posko & Petugas serta Rekap Lembar (Minggu 3 - 4)
- [ ] Pembuatan komponen layout dashboard global (Sidebar, Topbar, Breadcrumb, Periode Selector).
- [ ] Implementasi Dashboard Posko & Petugas (KPI Cards, grafik realisasi vs target threshold, tabel gap).
- [ ] Implementasi Dashboard Lembar Tunggakan (Rekapitulasi agregasi 1, 2, 3 lembar per petugas).
- [ ] Implementasi interaktivitas Modal Pop-up Drill-down untuk nama dan rincian pelanggan 2 & 3 lembar.

## Milestone 3: Fitur Spesifik Dabes, Kode Kelompok & Audit Bayar Tgl 21+ (Minggu 5 - 6)
- [ ] Implementasi Dashboard Tagihan Dabes (Filter daya $\ge 53\text{ kVA}$, formula kalkulasi rata-rata tanggal bayar 12 bulan).
- [ ] Banner early warning alert untuk pelanggan Dabes yang melampaui tanggal rata-rata bayar.
- [ ] Implementasi Dashboard Tren Kode Kelompok (Grafik distribusi kurva tanggal pelunasan per Kogol).
- [ ] Implementasi Dashboard Pelanggan Bayar Tanggal 21 ke Atas + streaming export Excel (.xlsx) instan.

## Milestone 4: UAT, Hardening, & Deployment Lapangan (Minggu 7 - 8)
- [ ] Stress-testing bulk import dengan simulasi 50.000 baris data Master dan Transaksi Harian.
- [ ] Verifikasi akurasi perhitungan rupiah tagihan dan gap threshold terhadap data eksisting PLN.
- [ ] Penerapan Role-Based Access Control (RBAC) untuk Super Admin UP3, Koordinator Posko, dan Petugas.
- [ ] Deployment ke production server PLN UP3 Indramayu dengan konfigurasi Nginx, PHP 8.3+, dan Redis Worker.