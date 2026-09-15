# AGENTS & SYSTEM AUTOMATION SPECIFICATION
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

Dokumen ini mendefinisikan aturan kerja otomatisasi, scheduled tasks, background workers, dan agen sistem cerdas yang beroperasi di balik layar.

---

## 1. Background Jobs & Worker Architecture

### A. `ProcessMasterDataJob`
- **Pemicu**: Upload file Master Data bulanan (DKRP).
- **Tugas**:
  1. Membuka streaming reader (`FastExcel`).
  2. Memvalidasi baris data (IDPEL 12 digit, KDDK, nominal RPTAG).
  3. Menjalankan *batch upsert* (1.000 record/chunk) ke tabel `pelanggan` dan `tagihan_rekening_bulanan`.
  4. Mencatat progres baris sukses dan gagal ke tabel `upload_log`.

### B. `ProcessDailyTransactionJob`
- **Pemicu**: Upload file transaksi harian.
- **Tugas**:
  1. Membaca transaksi pelunasan.
  2. Menyimpan baris transaksi ke `transaksi_pembayaran_harian`.
  3. Menjalankan rekonsiliasi seketika:
     ```sql
     UPDATE tagihan_rekening_bulanan
     SET status_lunas = TRUE, tgl_lunas = transactions.tgl_transaksi
     FROM transactions
     WHERE tagihan_rekening_bulanan.idpel = transactions.idpel
       AND tagihan_rekening_bulanan.thblrek = transactions.thblrek;
     ```

---

## 2. Scheduled Cron Tasks (`routes/console.php` / `app/Console/Kernel.php`)

```php
use Illuminate\Support\Facades\Schedule;

// 1. Evaluasi Pelanggan Dabes (Daya Besar) setiap jam 07:00 pagi
Schedule::command('pln:evaluate-dabes-alerts')->dailyAt('07:00');

// 2. Refresh Materialized Views Agregasi Saldo Penagihan setiap jam kerja
Schedule::command('pln:refresh-monitoring-views')->hourly()->between('07:00', '18:00');

// 3. Bersihkan file upload temporer yang berumur lebih dari 7 hari
Schedule::command('pln:cleanup-temp-imports')->weekly();
```

---

## 3. Intelligent Anomaly & Alert Rules (Dabes Anomaly Agent)

Sistem secara berkala mengevaluasi setiap pelanggan daya besar ($\ge 53	ext{ kVA}$):
1. **Aturan Evaluasi**:
   - Ambil riwayat tanggal pembayaran 12 bulan terakhir untuk setiap IDPEL Dabes.
   - Hitung nilai median dan rata-rata tanggal bayar: $	ext{AvgDay} = 	ext{ROUND}(	ext{AVG}(	ext{EXTRACT(DAY FROM tgl\_transaksi)}))$.
   - Jika lembar tagihan bulan berjalan **belum lunas** dan $	ext{HariIni} > 	ext{AvgDay}$:
     - Set flag `is_alert_overdue = TRUE`.
     - Tampilkan peringatan prioritas tinggi di Dashboard Dabes.
     - Kirim notifikasi WhatsApp/Email otomatis kepada Koordinator Posko dan Petugas penanggung jawab.