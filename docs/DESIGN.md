# DESIGN SPECIFICATION: SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

## 0. Alur Bisnis Sumber Data (Konteks Sebelum Desain UI)

Sebelum data tampil di dashboard manapun, terdapat proses 2 lane (lihat `ARCHITECTURE.md` §1.1):
`Super Admin ekstrak data dari Back Office PLN Pusat → Analisis → Import ke Aplikasi → Data terupload → Filter/Dashboard`.
Seluruh wireframe di bawah ini adalah representasi dari tahap **"Filter by ..."** — yaitu titik di mana data yang sudah diupload ditampilkan, difilter, dan di-drill-down oleh pengguna.

## 1. Information Architecture (IA) & Navigation Structure

Sistem menggunakan layout standar dashboard enterprise PLN: **Sidebar Navigation (Kiri)**, **Top Navigation Bar (Header)**, dan **Dynamic Content Area**.

### A. Site Map & Navigation Hierarchy
```
[ROOT]
│
├── 1. DASHBOARD & MONITORING
│   ├── 1.1 Posko & Petugas (Realisasi Harian & Bulanan vs Target Threshold Tgl 20)
│   ├── 1.2 Tagihan Dabes (Monitoring Pelanggan Daya Besar >= 53 kVA & Notifikasi Keterlambatan)
│   ├── 1.3 Pola Kode Kelompok (Tren Tanggal Bayar per Kogol 0, 1, 2, 3, 9)
│   ├── 1.4 Pelanggan Bayar Tgl 21+ (Audit Pelanggan Terlambat / Kena Denda BK & Export Excel)
│   └── 1.5 Saldo Tunggakan Lembar (Monitoring 1, 2, 3 Lembar & Drill-down Modal Detail)
│
├── 2. MANAJEMEN DATA & BULK PIPELINE
│   ├── 2.1 Upload Excel (Daftar Rekening Bulanan, Transaksi Harian, Monitoring Saldo/Target)
│   └── 2.2 Riwayat & Log Sinkronisasi (Status Antrean Queue, Validasi Baris, Error Handling)
│
└── 3. MASTER DATA & PENGATURAN
    ├── 3.1 Mapping KDDK & Petugas (Penetapan Rute Penagihan KDDK ke Petugas & PBM)
    ├── 3.2 Master Posko & ULP (Hierarki UP3 Indramayu -> ULP -> Posko)
    └── 3.3 Manajemen User & Akses (Role: Super Admin UP3, Koordinator Posko, Supervisor Penagihan)
```

### B. Global Shell Layout Wireframe
```
+----------------------------------------------------------------------------------------------------+
|  [LOGO PLN] SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU         [Periode: Sept 2026] [User: Admin]|
+-------------------+--------------------------------------------------------------------------------+
| SIDEBAR MENU      | BREADCRUMB: Dashboard > Posko & Petugas                                        |
|                   +--------------------------------------------------------------------------------+
| [MONITORING]      |                                                                                |
|  * Posko & Petugas|                                                                                |
|  * Tagihan Dabes  |                                                                                |
|  * Kode Kelompok  |                               [ MAIN CONTENT AREA ]                            |
|  * Bayar Tgl 21+  |                                                                                |
|  * Saldo Lembar   |                                                                                |
|                   |                                                                                |
| [DATA & IMPORT]   |                                                                                |
|  * Upload Excel   |                                                                                |
|  * Log Sinkron    |                                                                                |
|                   |                                                                                |
| [PENGATURAN]      |                                                                                |
|  * Mapping KDDK   |                                                                                |
|  * Master Petugas |                                                                                |
+-------------------+--------------------------------------------------------------------------------+
```

---

## 2. Low-Fidelity Wireframes

### Halaman 1: Dashboard Posko & Petugas
*Fokus:* Evaluasi pencapaian target harian dan bulanan terhadap batas toleransi (*Threshold Tgl 20*) serta *gap* yang harus ditagih.

> **Catatan open item**: PRD menyebut "Posko" dan "Petugas" sebagai 2 dashboard terpisah (FR-04a/FR-04b), sedangkan wireframe ini menggabungkan keduanya dalam satu halaman dengan level agregasi berbeda (grafik = level Posko, tabel = level Petugas). Perlu dikonfirmasi ke client apakah cukup 1 halaman dengan toggle "Lihat per Posko / Lihat per Petugas", atau memang harus 2 halaman/menu terpisah di sidebar.

```
+----------------------------------------------------------------------------------------------------+
| DASHBOARD POSKO & PETUGAS                                                                          |
| Filter: [ ULP: Indramayu Kota v ] [ Posko: Semua/Kota1/Kota2/Kota3/Lohbener/Arahan v ] [ Periode v ]|
+----------------------------------------------------------------------------------------------------+
| [ KPI METRICS ]                                                                                    |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
| | TARGET THRESHOLD   | | REALISASI BULANAN  | | GAP TARGET SISA    | | REALISASI HARI INI |       |
| | Rp 352.740.518     | | Rp 184.210.400     | | Rp 168.530.118     | | Rp 14.850.000      |       |
| +--------------------+ +--------------------+ +--------------------+ +--------------------+       |
+----------------------------------------------------------------------------------------------------+
| [ GRAFIK REALISASI HARIAN & BULANAN ]                                                              |
| +------------------------------------------------------------------------------------------------+ |
| | (Bar Chart: Realisasi Harian vs Garis Ambang Batas Target Tgl 20)                              | |
| | Rp |          _                                                                                | |
| |    |  _   _  | | _                                   --- Garis Target Threshold Tgl 20         | |
| |    | | | | | | || | _                                                                          | |
| |    +-------------------------------------------------------------                              | |
| |     Tgl 1  2  3  4  5 ... 10  11  ... 20                                                       | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
| [ TABEL REALISASI PER POSKO & PETUGAS ]                                   [ Cari Petugas: ______ ] |
| +----+-------------+---------+------+-----------------+-----------------+---------------+--------+ |
| | No | Posko       | Petugas | PBM  | Saldo Awal (Rp) | Realisasi (Rp)  | Target (Rp)   | Gap %  | |
| +----+-------------+---------+------+-----------------+-----------------+---------------+--------+ |
| | 1  | Posko Kota 1| SEFIANA | QODIR| 112.711.395     | 28.549.319      | 84.162.076    | 34%    | |
| | 2  | Posko Kota 1| DARKAWI | QODIR| 92.381.966      | 32.069.511      | 60.312.455    | 35%    | |
| | 3  | Posko Kota 2| AGUS    | QODIR| 129.848.340     | 34.876.392      | 94.971.948    | 27%    | |
| +----+-------------+---------+------+-----------------+-----------------+---------------+--------+ |
+----------------------------------------------------------------------------------------------------+
```

---

### Halaman 2: Dashboard Tagihan Dabes (Daya Besar $\ge$ 53 kVA)
*Fokus:* Monitoring pelanggan prioritas revenue (B2, B3, I2, I3, P1) beserta *early alert* jika tanggal transaksi melewati rerata 1 tahun.

```
+----------------------------------------------------------------------------------------------------+
| DASHBOARD TAGIHAN DABES (DAYA BESAR >= 53 kVA)                                                     |
| Filter: [ Tarif: Semua (B2/I3/P1) v ] [ Status: Belum Lunas v ]               [ Cari IDPEL: _____ ]|
+----------------------------------------------------------------------------------------------------+
| [ NOTIFIKASI & EARLY WARNING BANNER ]                                                              |
| [!] PERINGATAN: 3 Pelanggan telah melewati tanggal rata-rata bayar tahunan!                        |
+----------------------------------------------------------------------------------------------------+
| [ SUMMARY CARDS ]                                                                                  |
| Total Pelanggan Dabes: 14 Plg | Belum Lunas: 5 Plg (Rp 186.400.000) | Lunas: 9 Plg (Rp 412.100.000) |
+----------------------------------------------------------------------------------------------------+
| [ TABEL MONITORING PELANGGAN DAYA BESAR ]                                                          |
| +--------------+--------------------+-------+--------+---------------+----------+--------+-------+ |
| | IDPEL        | Nama Pelanggan     | Tarif | Daya   | Tagihan (Rp)  | Avg Bayar| Status | Alert | |
| +--------------+--------------------+-------+--------+---------------+----------+--------+-------+ |
| | 533410014137 | PT MAJU JAYA TEXTIL| I3    | 520kVA | 63.325.324    | Tgl 10   | BELUM  | [!]   | |
| | 534030213845 | HOTEL CIPUTRA INDR | B2    | 105kVA | 12.160.791    | Tgl 12   | BELUM  | [!]   | |
| | 534030285971 | RS SENTOSA MEDIKA  | B2    | 66kVA  | 6.625.244     | Tgl 18   | BELUM  | [OK]  | |
| | 534030288945 | DINAS PU KABUPATEN | P1    | 105kVA | 7.824.235     | Tgl 08   | LUNAS  | -     | |
| +--------------+--------------------+-------+--------+---------------+----------+--------+-------+ |
+----------------------------------------------------------------------------------------------------+
| [ DETAIL HISTORIS & TREN PEMBAYARAN PELANGGAN ]                                                    |
| (Menampilkan kurva tanggal lunas 12 bulan terakhir vs tanggal hari ini)                            |
+----------------------------------------------------------------------------------------------------+
```

---

### Halaman 3: Dashboard Tren Kode Kelompok
*Fokus:* Distribusi dan pola tanggal pembayaran pelanggan berdasarkan kelompok tarif (Kogol 0, 1, 2, 3, 9).

```
+----------------------------------------------------------------------------------------------------+
| DASHBOARD TREN KODE KELOMPOK                                                                       |
| Filter: [ Kode Kelompok: Semua Kogol v ] [ Bulan Rekening: 2026-09 v ] [ Rentang Tgl: 1 - 30 v ]   |
+----------------------------------------------------------------------------------------------------+
| [ GRAFIK DISTRIBUSI TANGGAL PEMBAYARAN PER KODE KELOMPOK ]                                        |
| +------------------------------------------------------------------------------------------------+ |
| | % Bayar                                                                                        | |
| | 40% |                   == Kogol 0 (Rumah Tangga R1/R1M)                                       | |
| | 30% |          /\       -- Kogol 2 (Bisnis B1/B2)                                              | |
| | 20% |   /\    /  \      .. Kogol 3 (Industri)                                                  | |
| | 10% |  /  \--/----\..                                                                          | |
| |  0% +---------------------------------------------------------                                 | |
| |     Tgl 1 - 5    Tgl 6 - 10    Tgl 11 - 15    Tgl 16 - 20    Tgl >20 (Kena BK/Denda)           | |
| +------------------------------------------------------------------------------------------------+ |
+----------------------------------------------------------------------------------------------------+
| [ TABEL DISTRIBUSI REALISASI KELOMPOK ]                                                            |
| +----------+--------------------+-------------+-----------------+---------------+----------------+ |
| | Kode Kel | Deskripsi Golongan | Total Plg   | Terbayar <=Tgl20| Terbayar >Tgl20| Belum Lunas    | |
| +----------+--------------------+-------------+-----------------+---------------+----------------+ |
| | Kogol 0  | Rumah Tangga Murni | 10.200 Plg  | 6.800 (66%)     | 1.400 (14%)   | 2.000 (20%)    | |
| | Kogol 1  | Sosial (S1/S2)     | 450 Plg     | 310 (69%)       | 40 (9%)       | 100 (22%)      | |
| | Kogol 2  | Bisnis (B1/B2)     | 1.200 Plg   | 900 (75%)       | 150 (12%)     | 150 (13%)      | |
| | Kogol 3  | Industri           | 80 Plg      | 65 (81%)        | 10 (13%)      | 5 (6%)         | |
| +----------+--------------------+-------------+-----------------+---------------+----------------+ |
+----------------------------------------------------------------------------------------------------+
```

---

### Halaman 4: Dashboard Pelanggan Bayar Tanggal 21 ke Atas
*Fokus:* Monitoring pelanggan terlambat yang terkena denda Biaya Keterlambatan (BK) dengan aksi export file Excel.

```
+----------------------------------------------------------------------------------------------------+
| DASHBOARD PELANGGAN BAYAR TANGGAL 21 KE ATAS                                                      |
| Filter: [ Bulan Rekening: 2026-08 (Bulan Kemarin) v ] [ Posko: Semua v ] [ [v] Unduh Excel (.xlsx) ]|
+----------------------------------------------------------------------------------------------------+
| [ KPI STATISTIK KETERLAMBATAN ]                                                                    |
| Total Pelanggan Telat: 1.842 Plg | Total Pokok: Rp 215.340.000 | Total Denda BK: Rp 12.450.000     |
+----------------------------------------------------------------------------------------------------+
| [ TABEL AUDIT PELANGGAN TELAT BAYAR ]                                     [ Search: _____________ ]|
| +----+--------------+-------------------+-------+------+------+------------+------------+--------+ |
| | No | IDPEL        | Nama Pelanggan    | Tarif | Daya | KDDK | Tgl Bayar  | Tagihan(Rp)| BK(Rp) | |
| +----+--------------+-------------------+-------+------+------+------------+------------+--------+ |
| | 1  | 533410128133 | CARTANA           | R1    | 450  | ACA  | 2026-08-22 | 49.878     | 3.000  | |
| | 2  | 533410305486 | LASNI             | R1    | 900  | ACB  | 2026-08-24 | 39.533     | 3.000  | |
| | 3  | 533410617840 | KUWU ANWARUDIN    | B1    | 450  | ACC  | 2026-08-28 | 38.248     | 3.000  | |
| +----+--------------+-------------------+-------+------+------+------------+------------+--------+ |
| Menampilkan 1 - 25 dari 1.842 data                                     [ < Prev ] [ 1 ] 2 3 [ Next > ]|
+----------------------------------------------------------------------------------------------------+
```

---

### Halaman 5: Dashboard Lembar Tunggakan (1, 2, 3 Lembar)
*Fokus:* Rekap saldo lembar per petugas, dilengkapi interaktivitas klik angka 2 dan 3 lembar untuk menampilkan *modal drill-down*.

```
+----------------------------------------------------------------------------------------------------+
| DASHBOARD SALDO TUNGGAKAN LEMBAR                                                                   |
| Filter: [ ULP: Indramayu Kota v ] [ Posko: Posko Kota 1 v ] [ Posisi Saldo: Pagi / Sore v ]        |
+----------------------------------------------------------------------------------------------------+
| [ SUMMARY CARDS LEMBAR TUNGGAKAN ]                                                                 |
| +-------------------------+ +-------------------------+ +-------------------------+                |
| | 1 LEMBAR (Bln Berjalan) | | 2 LEMBAR (Nunggak 2 Bln)| | 3 LEMBAR (Nunggak >=3)  |                |
| | 9.310 Plg               | | 110 Plg                 | | 6 Plg                   |                |
| | Rp 1.159.896.781        | | Rp 27.863.436           | | Rp 2.549.468            |                |
| +-------------------------+ +-------------------------+ +-------------------------+                |
+----------------------------------------------------------------------------------------------------+
| [ TABEL MONITORING SALDO LEMBAR PER PETUGAS ]                                                      |
| (Klik angka pada kolom 2 Lembar / 3 Lembar untuk melihat rincian nama pelanggan)                  |
| +----+---------+------+----------------------+----------------------+----------------------+       |
| | No | Petugas | KDDK | 1 Lembar (Plg / Rp)  | 2 Lembar (Plg / Rp)  | 3 Lembar (Plg / Rp)  |       |
| +----+---------+------+----------------------+----------------------+----------------------+       |
| | 1  | SEFIANA | ACA  | 1.006 | 109.771.419  | [ 9 Plg | 2.939.976]*| -                    |       |
| | 2  | DARKAWI | ACB  | 780   | 92.381.966   | -                    | -                    |       |
| | 3  | AGUS    | ACD  | 1.068 | 125.297.661  | [13 Plg | 3.158.119]*| [ 1 Plg | 1.392.560]*|       |
| +----+---------+------+----------------------+----------------------+----------------------+       |
+----------------------------------------------------------------------------------------------------+

* MODAL POP-UP SAAT ANGKA 2 ATAU 3 LEMBAR DI-KLIK:
  +--------------------------------------------------------------------------------------+
  | RINCIAN PELANGGAN TUNGGAKAN - PETUGAS: AGUS (KDDK: ACD) - KATEGORI: 3 LEMBAR       [X]
  +--------------------------------------------------------------------------------------+
  | IDPEL        : 533410998811                                                         |
  | Nama         : H. SUWANDI                                                           |
  | Alamat       : JL RAYA KOTA NO 45, INDRAMAYU                                        |
  | Tarif / Daya : R1M / 2.200 VA                                                       |
  |                                                                                      |
  | Rincian Lembar Belum Lunas:                                                          |
  | - Rekening Juli 2026      : Rp 464.180  (Status: Belum Lunas)                        |
  | - Rekening Agustus 2026   : Rp 464.190  (Status: Belum Lunas)                        |
  | - Rekening September 2026 : Rp 464.190  (Status: Belum Lunas)                        |
  | Total Tunggakan (3 Lembar): Rp 1.392.560                                             |
  |                                                                                      |
  | Tindakan Cepat: [ Cetak Surat Peringatan (SP) ]  [ Jadwalkan Pemutusan Sementara ]   |
  +--------------------------------------------------------------------------------------+
```