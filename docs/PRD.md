# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

---

## 1. Latar Belakang & Problem Statement
PLN UP3 Indramayu mengelola puluhan ribu pelanggan pascabayar yang tersebar di beberapa Unit Layanan Pelanggan (ULP) seperti ULP Indramayu Kota, Jatibarang, Haurgeulis, dan Cikedung. Setiap bulan, data rekening pelanggan (Daftar Rekening Listrik / DKRP) terbit dalam volume masif (12.000+ baris per ULP dengan 89 atribut). Sementara itu, pelunasan tagihan berlangsung setiap hari melalui ribuan transaksi harian dari berbagai kanal pembayaran (PPOB, Bank, ATM, Minimarket).

Hingga saat ini, proses pengendalian target saldo penagihan, mitigasi lembar tunggakan (1, 2, dan 3 lembar), serta pengawasan pelanggan prioritas Daya Besar (Dabes $\ge 53\text{ kVA}$) masih dilakukan secara manual dengan mengolah spreadsheet terpisah atau tangkapan layar monitoring. Akibatnya:
1. Tidak ada visibilitas real-time terhadap pencapaian target harian dan bulanan per posko dan petugas.
2. Penanganan pelanggan daya besar sering terlambat karena tidak adanya deteksi dini (*early alert*) terhadap pola tanggal bayar.
3. Rekonsiliasi data pelanggan yang menunggak 2 atau 3 lembar membutuhkan waktu lama untuk menemukan nama dan alamat fisik pelanggan di lapangan.

## 2. Tujuan Produk (Product Goals)
- Membangun portal pemantauan penagihan terpusat dengan mekanisme bulk import berbasis file Excel untuk Master Data, Transaksi Harian, dan Target Saldo.
- Menyediakan 6 dashboard analitik operasional yang responsif:
  1. Dashboard Posko (Realisasi Harian & Bulanan vs Target Threshold Tgl 20).
  2. Dashboard Petugas (Realisasi Harian & Bulanan vs Target Threshold Tgl 20).
  3. Dashboard Tagihan Dabes (Tren tanggal bayar pelanggan $\ge 53\text{ kVA}$ dan notifikasi anomali).
  4. Dashboard Tren Kode Kelompok (Pola tanggal bayar berdasarkan Kogol 0, 1, 2, 3, 9).
  5. Dashboard Pelanggan Bayar Tanggal 21 ke Atas (Audit pelanggan kena denda BK + Export Excel).
  6. Dashboard Lembar Tunggakan (Rekap 1, 2, 3 lembar dengan drill-down modal rincian pelanggan).
- Memastikan performa import dan pelaporan tinggi tanpa *memory exhaust* melalui arsitektur streaming reader dan asynchronous queue.

## 3. Persona Pengguna
1. **Super Admin (UP3 / Kantor Induk)**:
   - Mengunggah file Master Data bulanan, Transaksi harian, dan Target.
   - Mengelola master referensi organisasi (UP3, ULP, Posko, Petugas, Mapping KDDK).
2. **Koordinator Posko & Supervisor Pelayanan Pelanggan (ULP)**:
   - Memantau pencapaian target threshold harian per posko dan rute petugas.
   - Mengawasi pelanggan daya besar (Dabes) yang berisiko melewati tanggal rata-rata bayar.
3. **Petugas Penagih Lapangan (Billman / Cater)**:
   - Menerima daftar kerja pelanggan menunggak 2 dan 3 lembar untuk tindakan penagihan atau pemutusan sementara.

## 4. Spesifikasi Fungsional (User Stories & Acceptance Criteria)
### A. Modul Bulk Import & Data Pipeline (Upload & Log Sinkron)
- **FR-01 (Halaman Upload Data)**: Menyediakan antarmuka drag-and-drop dan browser file untuk mengunggah Master Data (`.xls`/`.xlsx`), Transaksi Harian (`.xlsx`), dan Monitoring Target (`.xlsx`) dengan pemilihan periode rekening (THBLREK) dan ULP target.
- **FR-02 (Pemrosesan Antrean Background)**: Sistem memproses berkas secara asynchronous via background queue worker dengan memory-streaming reader tanpa memblokir aktivitas pengguna di browser.
- **FR-03 (Halaman Log Sinkronisasi)**: Menyediakan dashboard audit pemantauan proses berkas secara real-time, meliputi status antrean worker, progres pembacaan baris (total/sukses/gagal), filter riwayat, serta modal pop-up detail pesan kesalahan (error log audit) jika berkas gagal diproses.
- **FR-04 (Rekonsiliasi Otomatis)**: Sistem secara otomatis mengaitkan data pelunasan transaksi harian dengan lembar piutang rekening yang bersangkutan (`UPDATE status_lunas = TRUE, tgl_lunas = tgl_transaksi`).

### B. Dashboard Operasional
- **FR-05 (Posko & Petugas)**: Menampilkan grafik realisasi harian/bulanan terhadap garis *Target Threshold Tgl 20*, serta tabel perbandingan Saldo Awal, Realisasi, dan Sisa Gap (Rp dan %).
- **FR-06 (Tagihan Dabes)**: Memfilter otomatis pelanggan dengan daya $\ge 53.000\text{ VA}$. Menghitung rata-rata tanggal bayar dalam 12 bulan terakhir dan menampilkan banner peringatan jika tanggal hari ini telah melewati tanggal rata-rata pelanggan yang belum lunas.
- **FR-07 (Kode Kelompok)**: Visualisasi tren tanggal pembayaran (tanggal 1 s/d 31) yang dikelompokkan berdasarkan Kogol (0: Rumah Tangga, 1: Sosial, 2: Bisnis, 3: Industri, 9: Khusus).
- **FR-08 (Bayar Tgl 21+)**: Menampilkan data pelanggan yang melunasi tagihan pada tanggal 21 atau setelahnya pada bulan lalu (terkena denda BK), dilengkapi tombol *Export to Excel (.xlsx)*.
- **FR-09 (Lembar Tunggakan)**: Menampilkan tabel rekapitulasi jumlah pelanggan dan nominal rupiah untuk 1, 2, dan 3 lembar. Ketika angka 2 atau 3 lembar diklik, muncul modal interaktif yang menampilkan daftar IDPEL, nama, alamat, tarif, daya, dan rincian lembar rekening yang belum dibayar.

## 5. Non-Functional Requirements (NFR)
- **Performa**: Import 15.000 baris data selesai dalam < 30 detik melalui background queue. Halaman dashboard memuat data dalam < 1.5 detik.
- **Skalabilitas**: Mampu menampung data histori tagihan hingga minimal 24 bulan siklus rekening tanpa degradasi performa agregasi.
- **Keamanan**: Autentikasi berbasis session guard Laravel standar, proteksi CSRF, sanitasi input, dan otorisasi bertingkat berbasis Role (RBAC).
- **Kompatibilitas**: Desain antarmuka responsif pada resolusi desktop kantor (1366x768 s/d 1920x1080).