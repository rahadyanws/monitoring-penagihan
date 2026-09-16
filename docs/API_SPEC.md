# API SPECIFICATION & DATA CONTRACTS
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU (V2.4)

Dokumen ini mendefinisikan seluruh kontrak data (request, query params, headers, dan JSON response payload) yang menyuplai seluruh antarmuka web, filter dinamis, modal pop-up, slide-over drawer, dan modul ekspor berkas.

**Base URL:** `/api/v1`

**Format Standar:**
- Request Content-Type: `application/json` (kecuali upload: `multipart/form-data`)
- Response Content-Type: `application/json`
- Format Tanggal: `YYYY-MM-DD`
- Format Waktu: `ISO 8601` (contoh: `2026-09-11T08:30:00Z`)
- Format Periode: `YYYYMM` (contoh: `202609`)
- Format IDPEL: 12 digit numerik

---

## DAFTAR MODUL ENDPOINT

1. [Modul Upload & Manajemen Antrean](#1-modul-upload--manajemen-antrean)
2. [Modul Telemetri & Engine Worker](#2-modul-telemetri--engine-worker)
3. [Dashboard 1: Posko & Petugas](#3-dashboard-1-posko--petugas)
4. [Dashboard 2: Tagihan Dabes (≥ 53 kVA)](#4-dashboard-2-tagihan-dabes--53-kva)
5. [Dashboard 3: Tren Kode Kelompok](#5-dashboard-3-tren-kode-kelompok)
6. [Dashboard 4: Pelanggan Bayar Tgl 21 ke Atas](#6-dashboard-4-pelanggan-bayar-tgl-21-ke-atas)
7. [Dashboard 5: Saldo Tunggakan Lembar (1, 2, dan 3 Lembar)](#7-dashboard-5-saldo-tunggakan-lembar-1-2-dan-3-lembar)

---

## 1. Modul Upload & Manajemen Antrean

### 1.1 Upload Berkas Bulk Excel
**Endpoint:** `POST /api/v1/imports/upload`

**Content-Type:** `multipart/form-data`

**Request Body:**

| Field | Type | Required | Default | Deskripsi |
|-------|------|----------|---------|-----------|
| `file_type` | string | Ya | - | `MASTER_DATA` \| `DAILY_TRANSACTION` \| `TARGET_MONITORING` |
| `thblrek` | integer | Ya | - | Format `YYYYMM` (contoh: `202609`) |
| `unit_up` | string | Ya | - | Kode ULP (contoh: `53403`, `53401`, `53400`) |
| `validate_integrity` | boolean | Tidak | `true` | Validasi integritas IDPEL 12 digit |
| `file` | file binary | Ya | - | File Excel `.xls` atau `.xlsx` (Maksimal 50 MB) |

**Response (202 Accepted):**

```json
{
  "success": true,
  "message": "Berkas berhasil diunggah dan dijadwalkan ke dalam background queue worker.",
  "data": {
    "upload_id": 104,
    "file_name": "DAILY_TRANSACTION_20260910.xlsx",
    "file_type": "DAILY_TRANSACTION",
    "file_size_mb": 3.2,
    "thblrek": 202609,
    "unit_up": "53403",
    "status": "QUEUED",
    "created_at": "2026-09-11T08:30:00Z"
  }
}
```

---

### 1.2 Status Progress Pengunggahan
**Endpoint:** `GET /api/v1/imports/status/{upload_id}`

**Path Parameters:**

| Parameter | Type | Required | Deskripsi |
|-----------|------|----------|-----------|
| `upload_id` | integer | Ya | ID upload yang didapat dari response 1.1 |

**Response (200 OK):**

```json
{
  "upload_id": 103,
  "file_name": "MASTER_DATA_202609.xls",
  "file_type": "MASTER_DATA",
  "status": "PROCESSING",
  "progress": {
    "total_rows": 12194,
    "processed_rows": 8500,
    "success_rows": 8500,
    "failed_rows": 0,
    "percentage": 70.0,
    "estimated_remaining_seconds": 45
  },
  "started_at": "2026-09-11T08:10:00Z",
  "completed_at": null
}
```

**Status Values:** `QUEUED` | `PROCESSING` | `COMPLETED` | `FAILED`

---

### 1.3 Daftar Riwayat & Audit Log Sinkronisasi
**Endpoint:** `GET /api/v1/imports/logs`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `file_type` | string | Tidak | - | Filter tipe berkas |
| `status` | string | Tidak | `ALL` | `ALL` \| `COMPLETED` \| `PROCESSING` \| `FAILED` \| `QUEUED` |
| `start_date` | date | Tidak | - | Format `YYYY-MM-DD` |
| `end_date` | date | Tidak | - | Format `YYYY-MM-DD` |
| `search` | string | Tidak | - | Cari nama file atau ID |
| `page` | int | Tidak | 1 | Halaman |
| `per_page` | int | Tidak | 10 | Jumlah item per halaman |

**Response (200 OK):**

```json
{
  "data": [
    {
      "id": 105,
      "created_at": "2026-09-11 09:15:00",
      "file_name": "TARGET_SEPT_2026.xlsx",
      "file_size_formatted": "450 KB",
      "file_type": "TARGET_SALDO",
      "total_rows": 10,
      "success_rows": 10,
      "failed_rows": 0,
      "duration_seconds": 12,
      "status": "COMPLETED",
      "uploaded_by": "Admin Penagihan (UP3 Indramayu)"
    },
    {
      "id": 102,
      "created_at": "2026-09-10 17:00:12",
      "file_name": "DAILY_TRX_ERR_FORMAT.xlsx",
      "file_size_formatted": "94 KB",
      "file_type": "TRANSAKSI",
      "total_rows": 150,
      "success_rows": 0,
      "failed_rows": 150,
      "duration_seconds": 6,
      "status": "FAILED",
      "error_code": "ERROR_HEADER_MISMATCH",
      "error_message": "Kolom wajib 'IDPEL' atau 'RpTagihan' tidak ditemukan pada baris pertama lembar Sheet1.",
      "uploaded_by": "Admin ULP Indramayu Kota (ADM-ULP-53403)"
    }
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total_items": 4,
    "total_pages": 1
  }
}
```

---

### 1.4 Download Catatan Kesalahan Validasi (.txt)
**Endpoint:** `GET /api/v1/imports/logs/{upload_id}/download-error`

**Path Parameters:**

| Parameter | Type | Required | Deskripsi |
|-----------|------|----------|-----------|
| `upload_id` | integer | Ya | ID upload |

**Response:** Text stream attachment `error_log_upload_{upload_id}.txt`

---

## 2. Modul Telemetri & Engine Worker

### 2.1 Worker Telemetry & Status Kapasitas
**Endpoint:** `GET /api/v1/system/worker-telemetry`

**Response (200 OK):**

```json
{
  "engine_status": "READY",
  "worker_daemon_active": true,
  "active_daemons_count": 2,
  "worker_pool_nodes": ["Node-IDM-01", "Node-IDM-02"],
  "throughput_rows_per_second": 2450,
  "queue_capacity_percentage": 18.0,
  "success_rate_percentage": 98.4,
  "pending_jobs_count": 1,
  "current_processing_job": {
    "upload_id": 103,
    "description": "Master Data THBLREK 202609"
  }
}
```

**Engine Status Values:** `READY` | `BUSY` | `MAINTENANCE` | `OFFLINE`

---

### 2.2 Restart Worker Daemon
**Endpoint:** `POST /api/v1/system/worker-restart`

**Response (200 OK):**

```json
{
  "success": true,
  "message": "Signal restart berhasil dikirimkan ke worker pool supervisor."
}
```

---

## 3. Dashboard 1: Posko & Petugas

### 3.1 Ringkasan Realisasi & Performa Petugas
**Endpoint:** `GET /api/v1/dashboard/posko-petugas`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `ulp` | string | Tidak | `Indramayu Kota` | Nama ULP |
| `posko` | string | Tidak | `Semua Posko` | Nama Posko |
| `thblrek` | int | Tidak | `202609` | Periode |

**Response (200 OK):**

```json
{
  "meta": {
    "ulp": "Indramayu Kota",
    "posko": "Semua Posko",
    "thblrek": 202609,
    "last_sync_at": "2026-09-15 17:00:00"
  },
  "kpi_cards": {
    "target_threshold_rp": 352740518,
    "realisasi_bulanan_rp": 184210400,
    "realisasi_percentage": 52.2,
    "sisa_gap_target_rp": 168530118,
    "gap_percentage": 47.8,
    "realisasi_hari_ini_rp": 14850000,
    "growth_percentage_vs_kemarin": 12.4
  },
  "daily_chart": {
    "target_line_per_day_rp": 17600000,
    "days": [
      {"day": 1, "realisasi_rp": 4200000, "is_peak": false},
      {"day": 14, "realisasi_rp": 22400000, "is_peak": false},
      {"day": 15, "realisasi_rp": 28500000, "is_peak": true},
      {"day": 16, "realisasi_rp": 14850000, "is_today": true}
    ]
  },
  "table": {
    "total_saldo_awal_rp": 334941701,
    "total_realisasi_rp": 95495222,
    "total_target_rp": 239446479,
    "average_gap_percentage": 32.0,
    "rows": [
      {
        "no": 1,
        "posko": "Posko Kota 1",
        "petugas": "SEFIANA",
        "pbm": "QODIR",
        "saldo_awal_rp": 112711395,
        "realisasi_rp": 28549319,
        "target_rp": 84162076,
        "gap_percentage": 34.0
      },
      {
        "no": 2,
        "posko": "Posko Kota 1",
        "petugas": "DARKAWI",
        "pbm": "QODIR",
        "saldo_awal_rp": 92381966,
        "realisasi_rp": 32069511,
        "target_rp": 60312455,
        "gap_percentage": 35.0
      },
      {
        "no": 3,
        "posko": "Posko Kota 2",
        "petugas": "AGUS",
        "pbm": "QODIR",
        "saldo_awal_rp": 129848340,
        "realisasi_rp": 34876392,
        "target_rp": 94971948,
        "gap_percentage": 27.0
      }
    ]
  }
}
```

---

### 3.2 Export Laporan Posko & Petugas
**Endpoint:** `GET /api/v1/dashboard/posko-petugas/export`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `format` | string | Ya | - | `xlsx` \| `pdf` |
| `thblrek` | int | Tidak | `202609` | Periode |
| `ulp` | string | Tidak | `Indramayu Kota` | Nama ULP |
| `posko` | string | Tidak | `Semua Posko` | Nama Posko |

**Response:** Binary File Download `Laporan_Posko_Petugas_{thblrek}.xlsx` / `.pdf`

---

## 4. Dashboard 2: Tagihan Dabes (≥ 53 kVA)

### 4.1 Tabel Monitoring & Rekapitulasi Pelanggan Dabes
**Endpoint:** `GET /api/v1/dashboard/dabes`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `tarif` | string | Tidak | `ALL` | `ALL` \| `B2` \| `I3` \| `P1` |
| `status` | string | Tidak | `UNPAID` | `UNPAID` \| `PAID` \| `ALL` |
| `search` | string | Tidak | - | IDPEL atau Nama Pelanggan |
| `thblrek` | int | Tidak | `202609` | Periode |

**Response (200 OK):**

```json
{
  "summary": {
    "total_pelanggan": 14,
    "total_kapasitas_kva": 4820,
    "belum_lunas": {
      "count": 5,
      "total_rp": 186400000,
      "percentage_unpaid": 35.6,
      "alert_overdue_count": 3,
      "normal_unpaid_count": 2
    },
    "realisasi_lunas": {
      "count": 9,
      "total_rp": 412100000,
      "percentage_paid": 64.4
    }
  },
  "alert_banner": {
    "is_active": true,
    "overdue_count": 3,
    "message": "3 Pelanggan Daya Besar Melewati Tanggal Historis Rutin per hari ini (15 Sep 2026)."
  },
  "rows": [
    {
      "idpel": 533410014137,
      "nama": "PT MAJU JAYA TEXTIL",
      "unit_nama": "ULP Jatibarang",
      "gardu": "JT084",
      "tarif": "I3",
      "daya_kva": 520,
      "rp_tag": 63325324,
      "avg_tgl_bayar": 10,
      "status_lunas": false,
      "alert": {
        "code": "OVERDUE",
        "label": "Lewat 5 Hari",
        "severity": "CRITICAL"
      }
    },
    {
      "idpel": 534030213845,
      "nama": "HOTEL CIPUTRA INDR",
      "unit_nama": "ULP Indramayu Kota",
      "gardu": "IK012",
      "tarif": "B2",
      "daya_kva": 105,
      "rp_tag": 12160791,
      "avg_tgl_bayar": 12,
      "status_lunas": false,
      "alert": {
        "code": "OVERDUE",
        "label": "Lewat 3 Hari",
        "severity": "WARNING"
      }
    },
    {
      "idpel": 534030285971,
      "nama": "RS SENTOSA MEDIKA",
      "unit_nama": "ULP Haurgeulis",
      "gardu": "HG033",
      "tarif": "B2",
      "daya_kva": 66,
      "rp_tag": 6625244,
      "avg_tgl_bayar": 18,
      "status_lunas": false,
      "alert": {
        "code": "NORMAL",
        "label": "Masih Wajar (< Tgl 18)",
        "severity": "INFO"
      }
    },
    {
      "idpel": 534030288945,
      "nama": "DINAS PU KABUPATEN",
      "unit_nama": "ULP Indramayu Kota",
      "gardu": "IK002",
      "tarif": "P1",
      "daya_kva": 105,
      "rp_tag": 7824235,
      "avg_tgl_bayar": 8,
      "status_lunas": true,
      "tgl_lunas": "2026-09-07",
      "alert": {
        "code": "PAID",
        "label": "Lunas Tgl 07 Sept (-)",
        "severity": "SUCCESS"
      }
    }
  ]
}
```

**Alert Severity Values:** `CRITICAL` | `WARNING` | `INFO` | `SUCCESS`

---

### 4.2 Detail Tren 12 Bulan & Kontak PIC Pelanggan Terpilih
**Endpoint:** `GET /api/v1/dashboard/dabes/{idpel}/history`

**Path Parameters:**

| Parameter | Type | Required | Deskripsi |
|-----------|------|----------|-----------|
| `idpel` | bigint | Ya | ID Pelanggan 12 digit |

**Response (200 OK):**

```json
{
  "idpel": 533410014137,
  "nama": "PT MAJU JAYA TEXTIL",
  "sektor": "Industri Tekstil Menengah",
  "gardu": "JT084",
  "bulan_lalu_realisasi": {
    "thblrek": 202608,
    "rp_tagihan": 61800000,
    "tgl_lunas": "2026-08-10"
  },
  "history_12_months": [
    {"period": "Okt 25", "tgl_bayar": 9, "rp_tagihan": 59400000},
    {"period": "Nov 25", "tgl_bayar": 10, "rp_tagihan": 62100000},
    {"period": "Des 25", "tgl_bayar": 11, "rp_tagihan": 63400000},
    {"period": "Jan 26", "tgl_bayar": 8, "rp_tagihan": 58900000},
    {"period": "Feb 26", "tgl_bayar": 10, "rp_tagihan": 60100000},
    {"period": "Mar 26", "tgl_bayar": 12, "rp_tagihan": 64500000},
    {"period": "Apr 26", "tgl_bayar": 9, "rp_tagihan": 59200000},
    {"period": "Mei 26", "tgl_bayar": 10, "rp_tagihan": 61000000},
    {"period": "Jun 26", "tgl_bayar": 11, "rp_tagihan": 62800000},
    {"period": "Jul 26", "tgl_bayar": 9, "rp_tagihan": 60500000},
    {"period": "Agu 26", "tgl_bayar": 10, "rp_tagihan": 61800000},
    {"period": "Sep 26", "tgl_bayar": null, "rp_tagihan": 63325324, "status": "BELUM_LUNAS", "current_day": 15, "deviation_days": 5}
  ],
  "pic_pelanggan": {
    "nama": "Bpk. Hartono Wibisono",
    "jabatan": "Manager Keuangan & Operasional",
    "telepon": "+6281238499201"
  },
  "petugas_tl": {
    "nama": "Rusdianto",
    "posko": "Posko ULP Jatibarang",
    "peran": "TL Pelayanan & Penagihan Posko",
    "status": "ON DUTY"
  }
}
```

---

### 4.3 Pembuatan Work Order (WO) Kunjungan / SP1
**Endpoint:** `POST /api/v1/dashboard/dabes/{idpel}/wo-kunjungan`

**Path Parameters:**

| Parameter | Type | Required | Deskripsi |
|-----------|------|----------|-----------|
| `idpel` | bigint | Ya | ID Pelanggan 12 digit |

**Request Body:**

| Field | Type | Required | Deskripsi |
|-------|------|----------|-----------|
| `jenis_tindakan` | string | Ya | `KIRIM_SP1` \| `KUNJUNGAN_LAPANGAN` \| `JANJI_BAYAR` |
| `tgl_rencana_kunjungan` | date | Ya | Format `YYYY-MM-DD` |
| `catatan` | text | Tidak | Keterangan tindak lanjut |

**Response (201 Created):**

```json
{
  "success": true,
  "message": "Work order kunjungan berhasil diterbitkan untuk petugas Posko Jatibarang.",
  "wo_number": "WO-DABES-202609-0012"
}
```

---

## 5. Dashboard 3: Tren Kode Kelompok

### 5.1 Distribusi & Pola Bayar per Kogol
**Endpoint:** `GET /api/v1/dashboard/kode-kelompok`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `kelompok` | string | Tidak | `ALL` | `ALL` \| `0` \| `1` \| `2` \| `3` |
| `thblrek` | int | Tidak | `202609` | Periode |
| `date_range` | string | Tidak | `1-30` | `1-30` \| `1-20` \| `21-30` |

**Response (200 OK):**

```json
{
  "meta": {
    "thblrek": 202609,
    "last_sync_at": "2026-09-15 17:00:00"
  },
  "kpi_cards": [
    {
      "kogol": 0,
      "label": "KOGOL 0 (RUMAH TANGGA)",
      "total_plg": 10200,
      "percentage_of_total": 85.5,
      "tepat_waktu_count": 6800,
      "tepat_waktu_pct": 66.0,
      "denda_count": 1400,
      "denda_pct": 14.0
    },
    {
      "kogol": 1,
      "label": "KOGOL 1 (SOSIAL)",
      "total_plg": 450,
      "percentage_of_total": 3.8,
      "tepat_waktu_count": 310,
      "tepat_waktu_pct": 69.0,
      "denda_count": 40,
      "denda_pct": 9.0
    },
    {
      "kogol": 2,
      "label": "KOGOL 2 (BISNIS)",
      "total_plg": 1200,
      "percentage_of_total": 10.1,
      "tepat_waktu_count": 900,
      "tepat_waktu_pct": 75.0,
      "denda_count": 150,
      "denda_pct": 12.0
    },
    {
      "kogol": 3,
      "label": "KOGOL 3 (INDUSTRI)",
      "total_plg": 80,
      "percentage_of_total": 0.7,
      "tepat_waktu_count": 65,
      "tepat_waktu_pct": 81.0,
      "denda_count": 10,
      "denda_pct": 13.0
    }
  ],
  "chart_spline": {
    "intervals": ["Tgl 1 - 5", "Tgl 6 - 10", "Tgl 11 - 15", "Tgl 16 - 20", "Tgl > 20"],
    "series": [
      {
        "name": "Kogol 0 (Rumah Tangga R1/R1M)",
        "color": "#0066cc",
        "points": [15, 38, 20, 13, 14],
        "peak_point": {"interval": "Tgl 6 - 10", "value": "38%"}
      },
      {
        "name": "Kogol 2 (Bisnis B1/B2)",
        "color": "#ea580c",
        "points": [10, 25, 35, 18, 12],
        "peak_point": {"interval": "Tgl 11 - 15", "value": "35%"}
      },
      {
        "name": "Kogol 3 (Industri)",
        "color": "#1f2937",
        "points": [5, 12, 51, 19, 13],
        "peak_point": {"interval": "Tgl 11 - 15", "value": "51%"}
      }
    ]
  },
  "table_rows": [
    {
      "kogol": 0,
      "deskripsi": "Rumah Tangga Murni",
      "sub_deskripsi": "Tarif R1 / R1M (450 - 2.200 VA)",
      "total_plg": 10200,
      "terbayar_tepat": {"count": 6800, "pct": 66.0},
      "terbayar_denda": {"count": 1400, "pct": 14.0},
      "belum_lunas": {"count": 2000, "pct": 20.0},
      "rasio_disiplin": "66% Cukup"
    },
    {
      "kogol": 1,
      "deskripsi": "Sosial (S1/S2)",
      "sub_deskripsi": "Rumah Ibadah, Yayasan, Panti",
      "total_plg": 450,
      "terbayar_tepat": {"count": 310, "pct": 69.0},
      "terbayar_denda": {"count": 40, "pct": 9.0},
      "belum_lunas": {"count": 100, "pct": 22.0},
      "rasio_disiplin": "69% Baik"
    },
    {
      "kogol": 2,
      "deskripsi": "Bisnis (B1/B2)",
      "sub_deskripsi": "Ruko, Swalayan, Restoran",
      "total_plg": 1200,
      "terbayar_tepat": {"count": 900, "pct": 75.0},
      "terbayar_denda": {"count": 150, "pct": 12.0},
      "belum_lunas": {"count": 150, "pct": 13.0},
      "rasio_disiplin": "75% Sangat Baik"
    },
    {
      "kogol": 3,
      "deskripsi": "Industri",
      "sub_deskripsi": "Pabrik Pengolahan, Cold Storage",
      "total_plg": 80,
      "terbayar_tepat": {"count": 65, "pct": 81.0},
      "terbayar_denda": {"count": 10, "pct": 13.0},
      "belum_lunas": {"count": 5, "pct": 6.0},
      "rasio_disiplin": "81% Prima"
    }
  ],
  "table_total": {
    "label": "TOTAL UP3 INDRAMAYU",
    "total_plg": 11930,
    "total_terbayar_tepat": {"count": 8075, "pct": 67.7},
    "total_terbayar_denda": {"count": 1600, "pct": 13.4},
    "total_belum_lunas": {"count": 2255, "pct": 18.9},
    "rata_rata_rasio_disiplin": "67,7%"
  }
}
```

---

### 5.2 Export Laporan Kode Kelompok
**Endpoint:** `GET /api/v1/dashboard/kode-kelompok/export`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `format` | string | Ya | - | `xlsx` \| `pdf` |
| `thblrek` | int | Tidak | `202609` | Periode |
| `kelompok` | string | Tidak | `ALL` | `ALL` \| `0` \| `1` \| `2` \| `3` |

**Response:** Binary File Download `Laporan_Pola_Kode_Kelompok_{thblrek}.xlsx` / `.pdf`

---

## 6. Dashboard 4: Pelanggan Bayar Tgl 21 ke Atas

### 6.1 Data Audit Pelanggan Telat & Denda BK
**Endpoint:** `GET /api/v1/dashboard/late-payment`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `thblrek` | int | Tidak | `202608` | Periode (bulan lalu) |
| `unit` | string | Tidak | `ALL` | Filter unit ULP |
| `tarif` | string | Tidak | `ALL` | `ALL` \| `R1` \| `B1` \| `I1` \| `P1` |
| `search` | string | Tidak | - | IDPEL, Nama, atau KDDK |
| `page` | int | Tidak | 1 | Halaman |
| `per_page` | int | Tidak | 25 | Jumlah item per halaman |

**Response (200 OK):**

```json
{
  "meta": {
    "thblrek": 202608,
    "last_sync_at": "2026-09-15 17:00:00"
  },
  "kpi_cards": {
    "total_pelanggan_telat": 1842,
    "percentage_dari_total": 15.4,
    "total_pokok_tagihan_rp": 215340000,
    "average_pokok_rp": 116905,
    "total_bk_rp": 12450000,
    "average_hari_telat": 4.6,
    "median_hari_telat": 4
  },
  "temporal_distribution": {
    "ringan_21_23": {"count": 1120, "pct": 60.8, "rp_pokok": 128420000},
    "sedang_24_26": {"count": 482, "pct": 26.2, "rp_pokok": 56140000},
    "berat_27_31": {"count": 240, "pct": 13.0, "rp_pokok": 30780000}
  },
  "tariff_composition": {
    "r1": {"count": 1420, "pct": 77.1},
    "b1": {"count": 290, "pct": 15.7},
    "i_and_p": {"count": 132, "pct": 7.2}
  },
  "table_rows": [
    {
      "no": 1,
      "idpel": 533410128133,
      "nama": "CARTANA",
      "tarif": "R1",
      "daya": 450,
      "kddk": "ACA",
      "tgl_bayar": "2026-08-22",
      "telat_hari": 2,
      "tagihan_pokok_rp": 49878,
      "biaya_bk_rp": 3000,
      "status": "Lunas + BK"
    },
    {
      "no": 2,
      "idpel": 533410305486,
      "nama": "LASNI",
      "tarif": "R1",
      "daya": 900,
      "kddk": "ACB",
      "tgl_bayar": "2026-08-24",
      "telat_hari": 4,
      "tagihan_pokok_rp": 39533,
      "biaya_bk_rp": 3000,
      "status": "Lunas + BK"
    }
  ],
  "subtotal_recap": {
    "total_lembar": 1842,
    "total_pokok_rp": 215340000,
    "total_bk_rp": 12450000,
    "grand_total_rp": 227790000
  },
  "pagination": {
    "current_page": 1,
    "per_page": 25,
    "total_items": 1842,
    "total_pages": 74
  }
}
```

---

### 6.2 Ekspor Excel Pelanggan Bayar Tgl 21+
**Endpoint:** `GET /api/v1/dashboard/late-payment/export`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `thblrek` | int | Tidak | `202608` | Periode |
| `unit` | string | Tidak | `ALL` | Filter unit ULP |
| `tarif` | string | Tidak | `ALL` | `ALL` \| `R1` \| `B1` \| `I1` \| `P1` |

**Response:** Binary Stream `Pelanggan_Bayar_Tgl_21_Keatas_{thblrek}.xlsx`

---

## 7. Dashboard 5: Saldo Tunggakan Lembar (1, 2, dan 3 Lembar)

### 7.1 Ringkasan Lembar & Tabel Per Petugas
**Endpoint:** `GET /api/v1/dashboard/lembar`

**Query Params:**

| Parameter | Type | Required | Default | Deskripsi |
|-----------|------|----------|---------|-----------|
| `ulp` | string | Tidak | `Indramayu Kota` | Nama ULP |
| `posko` | string | Tidak | `Posko Kota 1` | Nama Posko |
| `cutoff_time` | string | Tidak | `SORE_17` | `SORE_17` \| `PAGI_08` \| `REALTIME` |
| `filter_priority` | boolean | Tidak | `false` | Filter hanya yang ada 2L/3L |
| `search` | string | Tidak | - | Cari petugas atau KDDK |

**Response (200 OK):**

```json
{
  "meta": {
    "ulp": "Indramayu Kota",
    "posko": "Posko Kota 1",
    "