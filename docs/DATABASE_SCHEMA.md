# DATABASE SCHEMA & DATA DICTIONARY
## SISTEM MONITORING PENAGIHAN PLN UP3 INDRAMAYU

---

## 1. Entity Relationship Overview
Basis data dirancang menggunakan kaidah normalisasi 3NF dengan relasi referensial yang ketat untuk memastikan integritas data keuangan rekening dan transaksi.

---

## 2. Definisi DDL (PostgreSQL Dialect)

```sql
-- 1. REFERENSI ORGANISASI & WILAYAH
CREATE TABLE ref_ulp (
    id_ulp VARCHAR(10) PRIMARY KEY, -- Contoh: '53403'
    nama_ulp VARCHAR(100) NOT NULL, -- Contoh: 'ULP INDRAMAYU KOTA'
    unit_ap VARCHAR(10) NOT NULL,   -- Contoh: '53IDM' (UP3 Indramayu)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE ref_posko (
    id_posko SERIAL PRIMARY KEY,
    id_ulp VARCHAR(10) REFERENCES ref_ulp(id_ulp) ON DELETE CASCADE,
    nama_posko VARCHAR(50) NOT NULL, -- 'KOTA 1', 'KOTA 2', 'KOTA 3', 'LOHBENER', 'ARAHAN'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed awal untuk ULP Indramayu Kota (5 posko, sesuai konfirmasi lapangan):
-- INSERT INTO ref_posko (id_ulp, nama_posko) VALUES
--   ('53403', 'KOTA 1'), ('53403', 'KOTA 2'), ('53403', 'KOTA 3'),
--   ('53403', 'LOHBENER'), ('53403', 'ARAHAN');
-- Setiap posko membawahi >= 1 petugas (relasi via tabel `petugas.id_posko`).

CREATE TABLE petugas (
    id_petugas SERIAL PRIMARY KEY,
    id_posko INT REFERENCES ref_posko(id_posko) ON DELETE SET NULL,
    nama_petugas VARCHAR(100) NOT NULL, -- Contoh: 'SEFIANA', 'DARKAWI'
    nama_pbm VARCHAR(100) NOT NULL,     -- Contoh: 'QODIR' (Pengawas Billman)
    no_telepon VARCHAR(20),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE mapping_kddk_petugas (
    kddk VARCHAR(20) PRIMARY KEY,       -- Contoh: 'ACA', 'ACB', 'WEAAEHA04600'
    id_petugas INT REFERENCES petugas(id_petugas) ON DELETE CASCADE,
    keterangan VARCHAR(100),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. MASTER PELANGGAN
CREATE TABLE pelanggan (
    idpel BIGINT PRIMARY KEY,           -- 12 Digit Unik ID Pelanggan PLN
    nama VARCHAR(150) NOT NULL,
    alamat VARCHAR(255),
    nobang VARCHAR(20),
    ketnobang VARCHAR(50),
    rt VARCHAR(10),
    rw VARCHAR(10),
    nodlmrt VARCHAR(20),
    ketnodlmrt VARCHAR(50),
    kodepos VARCHAR(10),
    kd_gardu VARCHAR(50),
    nama_gardu VARCHAR(100),
    kddk VARCHAR(30) NOT NULL,          -- Relasi rute penagihan
    unit_ap VARCHAR(10) NOT NULL,       -- '53IDM'
    unit_up VARCHAR(10) NOT NULL,       -- '53403'
    pemda INT DEFAULT 8,
    kogol SMALLINT NOT NULL,            -- ⚠️ PERLU KONFIRMASI CLIENT: dokumen awal asumsi 0=Rumah Tangga,
                                         -- 1=Sosial, 2=Bisnis, 3=Industri, 9=Khusus. Catatan lapangan terbaru
                                         -- menyebut 5 kelompok (Kogol 0-4). Nilai definitif & mapping deskripsi
                                         -- WAJIB diverifikasi dari data DKRP asli sebelum go-live Milestone 3.
    subkogol VARCHAR(10),
    tgl_cabut_pasang INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_pelanggan_kddk ON pelanggan(kddk);
CREATE INDEX idx_pelanggan_kogol ON pelanggan(kogol);

-- 3. REKENING BULANAN (DKRP / SALDO PIUTANG)
CREATE TABLE tagihan_rekening_bulanan (
    id_tagihan BIGSERIAL PRIMARY KEY,
    thblrek INT NOT NULL,               -- Format YYYYMM (misal: 202609)
    idpel BIGINT NOT NULL REFERENCES pelanggan(idpel) ON DELETE CASCADE,
    tarif VARCHAR(10) NOT NULL,
    daya INT NOT NULL,                  -- Dalam VA (misal: 450, 900, 53000, 105000)
    kdpt VARCHAR(20),
    kdpt_2 VARCHAR(20),
    kd_proses_klp VARCHAR(20),
    posting_billing INT,
    msg INT DEFAULT 0,
    rp_ptl NUMERIC(15,2) DEFAULT 0,
    rp_tb NUMERIC(15,2) DEFAULT 0,
    rp_ppn NUMERIC(15,2) DEFAULT 0,
    rp_bpju NUMERIC(15,2) DEFAULT 0,
    rp_bp_trafo NUMERIC(15,2) DEFAULT 0,
    rp_sewa_trafo NUMERIC(15,2) DEFAULT 0,
    rp_sewa_kap NUMERIC(15,2) DEFAULT 0,
    rp_angsa NUMERIC(15,2) DEFAULT 0,
    rp_angsb NUMERIC(15,2) DEFAULT 0,
    rp_angsc NUMERIC(15,2) DEFAULT 0,
    rp_mat NUMERIC(15,2) DEFAULT 0,
    rp_pln NUMERIC(15,2) DEFAULT 0,
    rp_tag NUMERIC(15,2) NOT NULL,      -- Pokok tagihan bulan rekening bersangkutan
    rpbk1 NUMERIC(15,2) DEFAULT 0,
    rpbk2 NUMERIC(15,2) DEFAULT 0,
    rpbk3 NUMERIC(15,2) DEFAULT 0,
    rp_lwbp NUMERIC(15,2) DEFAULT 0,
    rp_wbp NUMERIC(15,2) DEFAULT 0,
    rp_blok3 NUMERIC(15,2) DEFAULT 0,
    rp_kvarh NUMERIC(15,2) DEFAULT 0,
    kwh_lwbp NUMERIC(12,2) DEFAULT 0,
    kwh_wbp NUMERIC(12,2) DEFAULT 0,
    blok3 NUMERIC(12,2) DEFAULT 0,
    sla_lwbp BIGINT DEFAULT 0,
    sah_lwbp BIGINT DEFAULT 0,
    sla_wbp BIGINT DEFAULT 0,
    sah_wbp BIGINT DEFAULT 0,
    sla_kvarh BIGINT DEFAULT 0,
    sah_kvarh BIGINT DEFAULT 0,
    pemkwh NUMERIC(12,2) DEFAULT 0,
    jamnyala INT DEFAULT 0,
    pemkvarh INT DEFAULT 0,
    kelbkvarh INT DEFAULT 0,
    fakm INT DEFAULT 1,
    fakmkvarh NUMERIC(8,2) DEFAULT 0,
    dlpd VARCHAR(100),
    dlpd_lm VARCHAR(100),
    dlpd_fkm VARCHAR(100),
    dlpd_kvarh VARCHAR(100),
    dlpd_3bln VARCHAR(100),
    dlpd_jnsmutasi VARCHAR(100),
    dlpd_tgl_baca VARCHAR(100),
    jamnyala600 VARCHAR(20),
    jamnyala400 VARCHAR(20),
    status_lunas BOOLEAN DEFAULT FALSE,
    tgl_lunas DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_thblrek_idpel UNIQUE (thblrek, idpel)
);

CREATE INDEX idx_tagihan_thblrek_lunas ON tagihan_rekening_bulanan(thblrek, status_lunas);
CREATE INDEX idx_tagihan_daya ON tagihan_rekening_bulanan(daya);
CREATE INDEX idx_tagihan_idpel ON tagihan_rekening_bulanan(idpel);

-- 4. TRANSAKSI PELUNASAN HARIAN
CREATE TABLE transaksi_pembayaran_harian (
    id_transaksi BIGSERIAL PRIMARY KEY,
    no_urut INT,
    tgl_transaksi DATE NOT NULL,        -- Tanggal bayar aktual
    tgl_buku DATE NOT NULL,
    idpel BIGINT NOT NULL REFERENCES pelanggan(idpel) ON DELETE CASCADE,
    tarif VARCHAR(10) NOT NULL,
    daya INT NOT NULL,
    thblrek INT NOT NULL,               -- Bulan rekening yang dilunasi
    kode_kel SMALLINT,
    kdpp VARCHAR(30),
    rp_ptl NUMERIC(15,2) DEFAULT 0,
    rp_bpju NUMERIC(15,2) DEFAULT 0,
    rp_ppn NUMERIC(15,2) DEFAULT 0,
    rp_materai NUMERIC(15,2) DEFAULT 0,
    rp_lain NUMERIC(15,2) DEFAULT 0,
    rp_tagihan NUMERIC(15,2) NOT NULL,  -- Pokok yang terbayar
    rp_bk NUMERIC(15,2) DEFAULT 0,      -- Denda Biaya Keterlambatan
    petugas_bank VARCHAR(100),
    kode_status VARCHAR(10),            -- 'U' = Uang Masuk / Lunas
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_transaksi_tgl_transaksi ON transaksi_pembayaran_harian(tgl_transaksi);
CREATE INDEX idx_transaksi_thblrek ON transaksi_pembayaran_harian(thblrek);
CREATE INDEX idx_transaksi_idpel ON transaksi_pembayaran_harian(idpel);

-- 5. TARGET & MONITORING SALDO PETUGAS
CREATE TABLE monitoring_target_petugas (
    id_monitoring SERIAL PRIMARY KEY,
    thblrek INT NOT NULL,               -- Periode rekening (contoh: 202609)
    tgl_monitoring DATE NOT NULL,       -- Tanggal evaluasi saldo
    kddk VARCHAR(20) NOT NULL,
    id_petugas INT REFERENCES petugas(id_petugas) ON DELETE CASCADE,
    target_threshold_rp NUMERIC(15,2) NOT NULL DEFAULT 0,
    gap_threshold_rp NUMERIC(15,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_monitoring_periode_kddk UNIQUE (thblrek, tgl_monitoring, kddk)
);

-- 6. RIWAYAT UPLOAD FILE (AUDIT LOG)
CREATE TABLE upload_log (
    id_upload BIGSERIAL PRIMARY KEY,
    file_type VARCHAR(50) NOT NULL,     -- 'MASTER_DATA', 'DAILY_TRANSACTION', 'TARGET_MONITORING'
    file_name VARCHAR(255) NOT NULL,
    total_rows INT DEFAULT 0,
    success_rows INT DEFAULT 0,
    failed_rows INT DEFAULT 0,
    status VARCHAR(30) DEFAULT 'PENDING', -- 'PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'
    error_message TEXT,
    uploaded_by VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP
);
```