# ARCHITECTURE SPECIFICATION
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

---

## 1. Arsitektur Sistem Global (Fullstack Laravel Monolith)

Sistem dirancang dengan pendekatan **Modular Monolithic** memanfaatkan ekosistem resmi Laravel versi terbaru (Laravel 13), mengedepankan kesederhanaan deployment, efisiensi resource server, dan kecepatan eksekusi data.

```
+-------------------------------------------------------------------------+
|                          CLIENT BROWSER (UI)                            |
|       Blade Templates + Tailwind CSS v4 + Alpine.js / Livewire          |
+------------------------------------+------------------------------------+
                                     │ HTTP / HTTPS (Inertia / Livewire)
                                     ▼
+-------------------------------------------------------------------------+
|                        LARAVEL 13 APPLICATION SERVER                    |
|                                                                         |
|  [ Routing & Middleware ]                                               |
|      └── Web Routes, Auth Guard (Sanctum/Session), RBAC Check           |
|                                                                         |
|  [ Controller / Livewire Components ]                                   |
|      ├── DashboardPoskoController                                       |
|      ├── DashboardDabesController                                       |
|      ├── DashboardKogolController                                       |
|      ├── DashboardLatePaymentController                                 |
|      ├── DashboardLembarController                                      |
|      └── ImportUploadController                                         |
|                                                                         |
|  [ Service & Domain Logic Layer ]                                       |
|      ├── ExcelStreamingService (rap2hpoutre/fast-excel)                 |
|      ├── BillingReconciliationService                                   |
|      ├── DabesAnomalyDetectionService                                   |
|      └── ThresholdCalculationService                                    |
|                                                                         |
|  [ Asynchronous Queue Workers ]                                         |
|      ├── ProcessMasterDataJob                                           |
|      ├── ProcessDailyTransactionJob                                     |
|      └── ProcessTargetMonitoringJob                                     |
+-------------------+---------------------------------+-------------------+
                    │                                 │
                    ▼                                 ▼
+-----------------------------------+ +-----------------------------------+
|         DATABASE LAYER            | |          CACHE & QUEUE            |
|     PostgreSQL 16+ / MySQL 8+     | |         Redis 7+ / DB Queue       |
|  - Normalized Tables              | |  - Asynchronous Jobs              |
|  - Aggregation Views              | |  - Query Cache & Lock             |
|  - Composite B-Tree Indexes       | |                                   |
+-----------------------------------+ +-----------------------------------+
```

---

## 1.1 Alur Data End-to-End (Swimlane)

Proses bisnis dari sumber data hingga tampil di dashboard melibatkan dua aktor terpisah dan **tidak menggunakan integrasi API langsung** ke Back Office PLN Pusat:

```
Lane: SUPER ADMIN (di luar sistem)          Lane: APLIKASI DASHBOARD PLN UP3 INDRAMAYU
──────────────────────────────────          ────────────────────────────────────────────
(START)                                      (START)
   │                                            │
   ▼                                            │
[Ekstrak data dari Back Office PLN Pusat] ──Import──▶ [Data terupload]
   │                                            │
   ▼                                            ▼
[Melakukan Analisis] ─────────────────────────▶ [Filter by ...]
   │                                            │
   ▼                                            ▼
 (END)                                        (END)
```

- **Ekstrak data**: dilakukan manual/semi-manual oleh Super Admin dari sistem internal PLN Pusat (bukan proses yang berada dalam scope aplikasi ini).
- **Import**: titik kontak antara kedua lane — file hasil ekstraksi diunggah ke aplikasi melalui `POST /api/v1/imports/upload` (lihat `API_SPEC.md`), lalu diproses `ProcessMasterDataJob` / `ProcessDailyTransactionJob` (lihat `AGENTS.md`).
- **Melakukan Analisis** (lane Super Admin) berkorespondensi dengan **Filter by ...** (lane Aplikasi) — yaitu seluruh dashboard analitik (Posko, Petugas, Dabes, Kode Kelompok, Bayar Tgl 21+, Lembar) yang memungkinkan Super Admin/Koordinator melakukan filter & drill-down terhadap data yang sudah masuk.

Implikasi arsitektur: karena "Ekstrak data" berada di luar sistem, aplikasi tidak boleh berasumsi format file selalu identik antar periode — validasi header/kolom saat import harus eksplisit memberi pesan error yang jelas ke Super Admin.

## 2. Rincian Komponen Arsitektur

### A. Presentation Layer (Frontend Native)
- **Engine**: Laravel Blade Component + Tailwind CSS.
- **Interaktivitas Client**: Alpine.js untuk kontrol state lokal (toggle modal pop-up lembar 2/3, drop-down filter, tab view).
- **Visualisasi Grafik**: ApexCharts / Chart.js yang di-mount secara reaktif membaca payload JSON dari controller.
- **Komponen Real-time (Opsional/Opsional Lanjutan)**: Livewire v3 untuk filter tanpa reload halaman.

### B. Bulk Import & Data Processing Pipeline
Untuk menangani file Master Data berukuran besar (89 kolom, puluhan ribu baris) dan Transaksi Harian:
1. **Upload Request**: File diunggah melalui formulir, divalidasi MIME type dan format kolom header, lalu disimpan di `storage/app/imports/`.
2. **Dispatch Job**: Controller mendispatch `ProcessMasterDataJob` atau `ProcessDailyTransactionJob` ke antrean worker.
3. **Streaming Reader**: Menggunakan `rap2hpoutre/fast-excel` (berbasis `openspout`) yang membaca file baris demi baris menggunakan pointer memori kecil (< 20MB RAM).
4. **Batch Upsert**: Data di-buffer per 1.000 baris dan disimpan menggunakan `DB::table(...)->upsert()` untuk meminimalkan beban koneksi database.
5. **Auto-Reconciliation Event**: Selesai transaksi harian diimport, event `DailyTransactionProcessed` dipicu untuk memperbarui status lunas lembar rekening secara otomatis.

### C. Data Access & Query Optimization Strategy
- **Indexing Strategy**:
  - `pelanggan`: Index pada `kddk`, `unit_up`, `kogol`.
  - `tagihan_rekening_bulanan`: Composite Unique Index `(thblrek, idpel)`, Index pada `(status_lunas, thblrek)`, Index pada `(daya)` untuk filter Dabes.
  - `transaksi_pembayaran_harian`: Index pada `(tgl_transaksi, kode_kel)`, Index pada `(idpel, thblrek)`.
- **Database Views / Summary Table**:
  - Disediakan view agregasi harian untuk Dashboard Posko dan Dashboard Lembar agar query tidak melakukan aggregasi *full-table scan* berulang kali setiap user me-refresh dashboard.