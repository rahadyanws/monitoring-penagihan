# API SPECIFICATION & DATA ENDPOINTS
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

Meskipun sistem beroperasi sebagai Fullstack Laravel Monolith (Blade/Livewire), seluruh komponen analitik dashboard dan modal pop-up didukung oleh endpoint data JSON internal terstandarisasi.

---

## 1. Modul Upload & Data Pipeline

### `POST /api/v1/imports/upload`
Mengunggah file bulk Excel untuk diproses secara asynchronous oleh queue worker.
- **Content-Type**: `multipart/form-data`
- **Request Body**:
  - `file_type` (string, required): `MASTER_DATA` | `DAILY_TRANSACTION` | `TARGET_MONITORING`
  - `file` (file, required): `.xls` atau `.xlsx` (Max: 50MB)
  - `thblrek` (integer, optional): Format `YYYYMM`
- **Response (202 Accepted)**:
  ```json
  {
    "success": true,
    "message": "File berhasil diunggah dan sedang diproses di latar belakang.",
    "data": {
      "upload_id": 104,
      "file_name": "DAILY TRANSACTION_20260910.xlsx",
      "status": "PROCESSING",
      "queue_job_id": "job_998124"
    }
  }
  ```

### `GET /api/v1/imports/status/{upload_id}`
Memeriksa status progres eksekusi file upload.
- **Response (200 OK)**:
  ```json
  {
    "upload_id": 104,
    "file_type": "DAILY_TRANSACTION",
    "total_rows": 3902,
    "success_rows": 3902,
    "failed_rows": 0,
    "status": "COMPLETED",
    "completed_at": "2026-09-11 08:31:12"
  }
  ```

---

## 2. Dashboard Endpoints

### `GET /api/v1/dashboard/posko-petugas`
Mengambil data agregat realisasi harian/bulanan vs target threshold.
- **Query Params**: `ulp_id` (string), `posko_id` (int), `thblrek` (int), `tgl` (date)
- **Response (200 OK)**:
  ```json
  {
    "summary": {
      "target_threshold_rp": 352740518,
      "realisasi_bulanan_rp": 184210400,
      "gap_threshold_rp": 168530118,
      "realisasi_harian_rp": 14850000
    },
    "chart_realisasi_harian": [
      {"tgl": "2026-09-01", "realisasi_rp": 12500000, "target_line_rp": 17637025},
      {"tgl": "2026-09-02", "realisasi_rp": 18200000, "target_line_rp": 17637025}
    ],
    "petugas_rows": [
      {
        "id_petugas": 1,
        "petugas": "SEFIANA",
        "pbm": "QODIR",
        "kddk": "ACA",
        "saldo_awal_rp": 112711395,
        "realisasi_rp": 28549319,
        "target_threshold_rp": 84162076,
        "gap_percentage": 33.9
      }
    ]
  }
  ```

### `GET /api/v1/dashboard/dabes`
Mengambil daftar monitoring pelanggan daya besar $\ge 53\text{ kVA}$ beserta flag deteksi dini keterlambatan.
- **Query Params**: `thblrek` (int), `status_lunas` (bool)
- **Response (200 OK)**:
  ```json
  {
    "total_dabes": 14,
    "total_belum_lunas": 5,
    "nominal_belum_lunas_rp": 186400000,
    "items": [
      {
        "idpel": 533410014137,
        "nama": "PT MAJU JAYA TEXTIL",
        "tarif": "I3",
        "daya": 520000,
        "rp_tag": 63325324,
        "avg_tgl_bayar": 10,
        "hari_ini_tgl": 15,
        "status_lunas": false,
        "is_alert_overdue": true
      }
    ]
  }
  ```

### `GET /api/v1/dashboard/kode-kelompok`
Mengambil tren tanggal bayar pelanggan berdasarkan Kode Kelompok / Kogol.
- **Query Params**: `thblrek` (int)
- **Response (200 OK)**:
  ```json
  {
    "trend_per_kogol": {
      "kogol_0": [{"rentang": "1-5", "persen": 20}, {"rentang": "6-10", "persen": 40}],
      "kogol_2": [{"rentang": "1-5", "persen": 30}, {"rentang": "6-10", "persen": 45}]
    },
    "table_summary": [
      {
        "kogol": 0,
        "deskripsi": "Rumah Tangga (R1/R1M)",
        "total_plg": 10200,
        "lunas_sebelum_20": 6800,
        "lunas_setelah_20": 1400,
        "belum_lunas": 2000
      }
    ]
  }
  ```

### `GET /api/v1/dashboard/late-payment/export`
Mengekspor daftar pelanggan bayar tanggal 21 ke atas ke file `.xlsx`.
- **Query Params**: `thblrek` (int, default: bulan_lalu), `posko_id` (optional)
- **Response**: Binary streaming download `Pelanggan_Bayar_Tgl_21_Keatas_{thblrek}.xlsx`.

### `GET /api/v1/dashboard/lembar/detail-pelanggan`
Menampilkan modal drill-down rincian pelanggan untuk kategori 2 atau 3 lembar.
- **Query Params**: `kddk` (string, required), `lembar` (int: 2 atau 3)
- **Response (200 OK)**:
  ```json
  {
    "kddk": "ACD",
    "petugas": "AGUS",
    "lembar": 3,
    "pelanggan": [
      {
        "idpel": 533410998811,
        "nama": "H. SUWANDI",
        "alamat": "JL RAYA KOTA NO 45, INDRAMAYU",
        "tarif": "R1M",
        "daya": 2200,
        "total_rp_tagihan": 1392560,
        "unpaid_bills": [
          {"thblrek": 202607, "rp_tag": 464180},
          {"thblrek": 202608, "rp_tag": 464190},
          {"thblrek": 202609, "rp_tag": 464190}
        ]
      }
    ]
  }
  ```