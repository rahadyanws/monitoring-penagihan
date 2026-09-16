<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder untuk PROTOTYPE PRESENTASI.
     * Tidak melalui pipeline import Excel asli (ProcessMasterDataJob dkk) —
     * data langsung di-generate agar 6 dashboard bisa didemokan tanpa menunggu
     * proses upload production. Lihat README-PROTOTYPE.md.
     */
    public function run(): void
    {
        $this->call([
            OrganisasiSeeder::class,
            PelangganTagihanSeeder::class,
            MonitoringTargetSeeder::class,
            UploadLogSeeder::class,
        ]);
    }
}
