<?php

namespace Database\Seeders;

use App\Models\MappingKddkPetugas;
use App\Models\Pelanggan;
use App\Models\TagihanRekeningBulanan;
use App\Models\TransaksiPembayaranHarian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PelangganTagihanSeeder extends Seeder
{
    // 4 contoh pelanggan Dabes persis dari API_SPEC.md / DESIGN.md, supaya
    // demo dashboard Dabes identik dengan wireframe yang sudah dilihat client.
    protected array $dabesContoh = [
        ['idpel' => 533410014137, 'nama' => 'PT MAJU JAYA TEXTIL', 'tarif' => 'I3', 'daya' => 520000, 'rp_tag' => 63325324, 'avg_tgl_bayar' => 10],
        ['idpel' => 534030213845, 'nama' => 'HOTEL CIPUTRA INDR', 'tarif' => 'B2', 'daya' => 105000, 'rp_tag' => 12160791, 'avg_tgl_bayar' => 12],
        ['idpel' => 534030285971, 'nama' => 'RS SENTOSA MEDIKA', 'tarif' => 'B2', 'daya' => 66000, 'rp_tag' => 6625244, 'avg_tgl_bayar' => 18],
        ['idpel' => 534030288945, 'nama' => 'DINAS PU KABUPATEN', 'tarif' => 'P1', 'daya' => 105000, 'rp_tag' => 7824235, 'avg_tgl_bayar' => 8],
    ];

    public function run(): void
    {
        $faker = fake('id_ID');
        $now = Carbon::now();
        $thisMonth = (int) $now->format('Ym');
        $lastMonth = (int) $now->copy()->subMonthNoOverflow()->format('Ym');
        $twoMonthsAgo = (int) $now->copy()->subMonthsNoOverflow(2)->format('Ym');
        $todayDay = min(max($now->day, 8), 26); // clamp supaya demo selalu punya progres realistis

        $kddkList = MappingKddkPetugas::pluck('kddk')->all();
        $idpelCounter = 533410000000;

        // Distribusi Kogol: 0=Rumah Tangga, 1=Sosial, 2=Bisnis, 3=Industri, 9=Khusus
        $kogolWeights = [0 => 70, 1 => 5, 2 => 15, 3 => 5, 9 => 5];
        $kogolPool = [];
        foreach ($kogolWeights as $kogol => $weight) {
            $kogolPool = array_merge($kogolPool, array_fill(0, $weight, $kogol));
        }

        $dabesTargetCount = 14; // sesuai contoh "Total Pelanggan Dabes: 14 Plg" di DESIGN.md
        $dabesCreated = 0;

        foreach ($kddkList as $kddk) {
            $jumlahPelanggan = random_int(35, 70);

            for ($i = 0; $i < $jumlahPelanggan; $i++) {
                $kogol = $kogolPool[array_rand($kogolPool)];
                $idpel = $idpelCounter++;

                // Paksa beberapa pelanggan Kogol besar (2/3/9) jadi Dabes (daya >= 53.000 VA)
                $jadikanDabes = in_array($kogol, [2, 3, 9]) && $dabesCreated < $dabesTargetCount && random_int(1, 100) <= 10;

                [$daya, $tarif] = $this->tentukanDayaTarif($kogol, $jadikanDabes);

                $pelanggan = Pelanggan::create([
                    'idpel' => $idpel,
                    'nama' => $this->buatNama($faker, $kogol),
                    'alamat' => $faker->address(),
                    'kddk' => $kddk,
                    'unit_ap' => '53IDM',
                    'unit_up' => '53403',
                    'kogol' => $kogol,
                ]);

                $rpTag = $this->tentukanRpTag($daya);

                // Tagihan bulan berjalan (selalu ada = "1 lembar" baseline)
                $tagihanBulanIni = TagihanRekeningBulanan::create([
                    'thblrek' => $thisMonth,
                    'idpel' => $idpel,
                    'tarif' => $tarif,
                    'daya' => $daya,
                    'rp_tag' => $rpTag,
                    'status_lunas' => false,
                ]);

                // 4% pelanggan: tunggak 2 lembar (bulan lalu juga belum lunas)
                // 1% pelanggan: tunggak 3 lembar (2 bulan lalu ikut belum lunas)
                $roll = random_int(1, 100);
                if ($roll <= 1) {
                    $this->buatTagihanNunggak($idpel, $tarif, $daya, $lastMonth);
                    $this->buatTagihanNunggak($idpel, $tarif, $daya, $twoMonthsAgo);
                } elseif ($roll <= 5) {
                    $this->buatTagihanNunggak($idpel, $tarif, $daya, $lastMonth);
                }

                if ($jadikanDabes) {
                    $dabesCreated++;
                    $avgHari = random_int(5, 22);
                    $this->buatRiwayatDabes($idpel, $tarif, $daya, $rpTag, $avgHari, $now, $tagihanBulanIni, $todayDay);

                    continue; // Dabes sudah ditangani riwayat & status lunasnya sendiri
                }

                // Simulasi pembayaran bulan berjalan (progres s.d. "hari ini")
                $hariBayar = $this->acakHariBayar($kogol);
                if ($hariBayar <= $todayDay) {
                    $tglBayar = $now->copy()->startOfMonth()->addDays($hariBayar - 1);
                    $tagihanBulanIni->update(['status_lunas' => true, 'tgl_lunas' => $tglBayar]);
                    $this->buatTransaksi($idpel, $tarif, $daya, $thisMonth, $rpTag, $tglBayar);
                }

                // Simulasi transaksi BULAN LALU (siklus sudah selesai) -> untuk dashboard
                // Kode Kelompok & Bayar Tgl 21+. ~92% pelanggan dianggap sudah bayar.
                if (random_int(1, 100) <= 92) {
                    $hariBayarLalu = $this->acakHariBayar($kogol, true);
                    $tglBayarLalu = Carbon::createFromFormat('Ym', (string) $lastMonth)->startOfMonth()->addDays($hariBayarLalu - 1);
                    $this->buatTransaksi($idpel, $tarif, $daya, $lastMonth, $rpTag, $tglBayarLalu);
                }
            }
        }

        // Lengkapi 4 contoh Dabes persis dari dokumen (idpel unik, ditambahkan terpisah)
        $this->seedDabesContoh($kddkList[0] ?? 'ACA', $now, $todayDay);
    }

    protected function buatNama($faker, int $kogol): string
    {
        if (in_array($kogol, [2, 3, 9])) {
            $label = match ($kogol) {
                2 => ['TOKO', 'CV', 'UD', 'WARUNG'],
                3 => ['PT', 'PABRIK', 'CV'],
                default => ['KANTOR', 'DINAS', 'YAYASAN'],
            };

            return $label[array_rand($label)] . ' ' . strtoupper($faker->lastName());
        }

        return strtoupper($faker->name());
    }

    protected function tentukanDayaTarif(int $kogol, bool $dabes): array
    {
        if ($dabes) {
            $daya = [53000, 66000, 82500, 105000, 147000, 197000][array_rand([53000, 66000, 82500, 105000, 147000, 197000])];
            $tarif = match ($kogol) {
                3 => 'I3',
                9 => 'P1',
                default => 'B2',
            };

            return [$daya, $tarif];
        }

        return match ($kogol) {
            0 => [[450, 900, 1300, 2200, 3500][array_rand([450, 900, 1300, 2200, 3500])], 'R1'],
            1 => [[900, 1300, 2200][array_rand([900, 1300, 2200])], 'S1'],
            2 => [[900, 2200, 3500, 5500][array_rand([900, 2200, 3500, 5500])], 'B1'],
            3 => [[3500, 5500, 11000][array_rand([3500, 5500, 11000])], 'I2'],
            default => [[2200, 3500][array_rand([2200, 3500])], 'P2'],
        };
    }

    protected function tentukanRpTag(int $daya): float
    {
        return match (true) {
            $daya >= 53000 => random_int(5_000_000, 70_000_000),
            $daya >= 3500 => random_int(300_000, 3_000_000),
            default => random_int(40_000, 350_000),
        };
    }

    protected function acakHariBayar(int $kogol, bool $siklusSelesai = false): int
    {
        // Sebaran hari bayar per Kogol (1-31), dipakai untuk Dashboard Kode Kelompok
        // dan Dashboard Bayar Tgl 21 ke Atas. Kogol 0 (Rumah Tangga) punya ekor lebih
        // panjang ke tanggal >20 dibanding Kogol industri/bisnis yang cenderung bayar awal bulan.
        $bucket = match ($kogol) {
            0, 1 => ['1-5' => 10, '6-10' => 20, '11-15' => 25, '16-20' => 20, '21-31' => 25],
            default => ['1-5' => 35, '6-10' => 30, '11-15' => 20, '16-20' => 10, '21-31' => 5],
        };

        $roll = random_int(1, 100);
        $cum = 0;
        foreach ($bucket as $range => $weight) {
            $cum += $weight;
            if ($roll <= $cum) {
                [$min, $max] = array_map('intval', explode('-', $range));
                $max = $siklusSelesai ? min($max, 31) : min($max, 28);

                return random_int($min, $max);
            }
        }

        return 15;
    }

    protected function buatTagihanNunggak(int $idpel, string $tarif, int $daya, int $thblrek): void
    {
        TagihanRekeningBulanan::create([
            'thblrek' => $thblrek,
            'idpel' => $idpel,
            'tarif' => $tarif,
            'daya' => $daya,
            'rp_tag' => $this->tentukanRpTag($daya),
            'status_lunas' => false,
        ]);
    }

    protected function buatTransaksi(int $idpel, string $tarif, int $daya, int $thblrek, float $rpTag, Carbon $tglBayar): void
    {
        $bk = $tglBayar->day >= 21 ? 3000 : 0; // Denda Biaya Keterlambatan (BK) flat, sesuai contoh DESIGN.md

        TransaksiPembayaranHarian::create([
            'tgl_transaksi' => $tglBayar,
            'idpel' => $idpel,
            'tarif' => $tarif,
            'daya' => $daya,
            'thblrek' => $thblrek,
            'rp_tagihan' => $rpTag,
            'rp_bk' => $bk,
            'kode_status' => 'U',
        ]);
    }

    protected function buatRiwayatDabes(int $idpel, string $tarif, int $daya, float $rpTag, int $avgHari, Carbon $now, TagihanRekeningBulanan $tagihanBulanIni, int $todayDay): void
    {
        // 11 bulan riwayat ke belakang, tiap bulan bayar di sekitar $avgHari (jitter kecil)
        for ($m = 11; $m >= 1; $m--) {
            $periode = $now->copy()->subMonthsNoOverflow($m);
            $hari = max(1, min(27, $avgHari + random_int(-3, 3)));
            $tglBayar = $periode->copy()->startOfMonth()->addDays($hari - 1);

            $this->buatTransaksi($idpel, $tarif, $daya, (int) $periode->format('Ym'), $rpTag, $tglBayar);
        }

        // Bulan berjalan: acak apakah pelanggan Dabes ini SUDAH bayar atau belum
        // (dipakai untuk memicu early-warning alert jika belum lunas & sudah lewat avgHari)
        $sudahBayarBulanIni = random_int(1, 100) <= 55;
        if ($sudahBayarBulanIni) {
            $hari = max(1, min($todayDay, $avgHari + random_int(-2, 2)));
            $tglBayar = $now->copy()->startOfMonth()->addDays(max(0, $hari - 1));
            $tagihanBulanIni->update(['status_lunas' => true, 'tgl_lunas' => $tglBayar]);
            $this->buatTransaksi($idpel, $tarif, $daya, (int) $now->format('Ym'), $rpTag, $tglBayar);
        }
    }

    protected function seedDabesContoh(string $kddk, Carbon $now, int $todayDay): void
    {
        $thisMonth = (int) $now->format('Ym');

        foreach ($this->dabesContoh as $c) {
            $pelanggan = Pelanggan::create([
                'idpel' => $c['idpel'],
                'nama' => $c['nama'],
                'alamat' => 'JL RAYA INDRAMAYU (CONTOH DOKUMEN)',
                'kddk' => $kddk,
                'unit_ap' => '53IDM',
                'unit_up' => '53403',
                'kogol' => $c['tarif'] === 'I3' ? 3 : ($c['tarif'] === 'P1' ? 9 : 2),
            ]);

            $tagihanBulanIni = TagihanRekeningBulanan::create([
                'thblrek' => $thisMonth,
                'idpel' => $c['idpel'],
                'tarif' => $c['tarif'],
                'daya' => $c['daya'],
                'rp_tag' => $c['rp_tag'],
                'status_lunas' => false,
            ]);

            $this->buatRiwayatDabesTetap($c, $now, $tagihanBulanIni, $todayDay);
        }
    }

    protected function buatRiwayatDabesTetap(array $c, Carbon $now, TagihanRekeningBulanan $tagihanBulanIni, int $todayDay): void
    {
        for ($m = 11; $m >= 1; $m--) {
            $periode = $now->copy()->subMonthsNoOverflow($m);
            $hari = max(1, min(27, $c['avg_tgl_bayar'] + random_int(-2, 2)));
            $tglBayar = $periode->copy()->startOfMonth()->addDays($hari - 1);
            $this->buatTransaksi($c['idpel'], $c['tarif'], $c['daya'], (int) $periode->format('Ym'), $c['rp_tag'], $tglBayar);
        }

        // DINAS PU KABUPATEN dibuat "LUNAS" bulan ini (sesuai contoh di DESIGN.md),
        // 3 lainnya dibuat "BELUM LUNAS" agar banner early-warning [!] muncul.
        if ($c['idpel'] === 534030288945) {
            $hari = max(1, min($todayDay, $c['avg_tgl_bayar']));
            $tglBayar = $now->copy()->startOfMonth()->addDays($hari - 1);
            $tagihanBulanIni->update(['status_lunas' => true, 'tgl_lunas' => $tglBayar]);
            $this->buatTransaksi($c['idpel'], $c['tarif'], $c['daya'], (int) $now->format('Ym'), $c['rp_tag'], $tglBayar);
        }
    }
}
