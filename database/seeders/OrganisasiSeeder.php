<?php

namespace Database\Seeders;

use App\Models\MappingKddkPetugas;
use App\Models\Petugas;
use App\Models\RefPosko;
use App\Models\RefUlp;
use Illuminate\Database\Seeder;

class OrganisasiSeeder extends Seeder
{
    // KDDK => [nama_petugas, nama_pbm, nama_posko]
    // 3 baris pertama sengaja disamakan dengan contoh data di PRD.md & DESIGN.md
    // supaya prototype "nyambung" persis dengan wireframe yang sudah dipresentasikan.
    public array $petugasTetap = [
        ['ACA', 'SEFIANA', 'QODIR', 'KOTA 1'],
        ['ACB', 'DARKAWI', 'QODIR', 'KOTA 1'],
        ['ACD', 'AGUS', 'QODIR', 'KOTA 2'],
    ];

    public function run(): void
    {
        RefUlp::create([
            'id_ulp' => '53403',
            'nama_ulp' => 'ULP INDRAMAYU KOTA',
            'unit_ap' => '53IDM',
        ]);

        $namaPosko = ['KOTA 1', 'KOTA 2', 'KOTA 3', 'LOHBENER', 'ARAHAN'];
        $poskoMap = [];
        foreach ($namaPosko as $nama) {
            $poskoMap[$nama] = RefPosko::create([
                'id_ulp' => '53403',
                'nama_posko' => $nama,
            ]);
        }

        $faker = fake('id_ID');
        $kddkPool = ['ACC', 'ACE', 'ACF', 'ACG', 'ACH', 'ADA', 'ADB', 'ADC', 'AEA', 'AEB', 'AFA', 'AFB'];
        $kddkPoolIndex = 0;

        // 1. Petugas tetap (samakan dengan contoh dokumen)
        foreach ($this->petugasTetap as [$kddk, $nama, $pbm, $posko]) {
            $petugas = Petugas::create([
                'id_posko' => $poskoMap[$posko]->id_posko,
                'nama_petugas' => $nama,
                'nama_pbm' => $pbm,
                'no_telepon' => $faker->phoneNumber(),
                'is_active' => true,
            ]);
            MappingKddkPetugas::create([
                'kddk' => $kddk,
                'id_petugas' => $petugas->id_petugas,
                'keterangan' => "Rute {$posko}",
            ]);
        }

        // 2. Petugas tambahan agar tiap posko punya 3-4 petugas (kebutuhan demo dashboard)
        foreach ($namaPosko as $nama) {
            $jumlahSaatIni = Petugas::where('id_posko', $poskoMap[$nama]->id_posko)->count();
            $tambahan = max(0, 3 - $jumlahSaatIni);

            for ($i = 0; $i < $tambahan; $i++) {
                $petugas = Petugas::create([
                    'id_posko' => $poskoMap[$nama]->id_posko,
                    'nama_petugas' => strtoupper($faker->firstName()),
                    'nama_pbm' => strtoupper($faker->firstName()),
                    'no_telepon' => $faker->phoneNumber(),
                    'is_active' => true,
                ]);

                $kddk = $kddkPool[$kddkPoolIndex++] ?? ('KD' . $kddkPoolIndex);
                MappingKddkPetugas::create([
                    'kddk' => $kddk,
                    'id_petugas' => $petugas->id_petugas,
                    'keterangan' => "Rute {$nama}",
                ]);
            }
        }
    }
}
