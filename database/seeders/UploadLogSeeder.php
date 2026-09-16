<?php

namespace Database\Seeders;

use App\Models\UploadLog;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UploadLogSeeder extends Seeder
{
    /**
     * 4 contoh riwayat upload persis seperti wireframe Riwayat & Log Sinkronisasi.html,
     * supaya halaman ini langsung terlihat "hidup" begitu prototype dibuka pertama kali.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $thblrek = (int) $now->format('Ym');

        UploadLog::create([
            'file_type' => 'TARGET_MONITORING',
            'file_name' => 'TARGET_SEPT_2026.xlsx',
            'file_size_bytes' => 450 * 1024,
            'thblrek' => $thblrek,
            'id_ulp' => '53403',
            'total_rows' => 10,
            'success_rows' => 10,
            'failed_rows' => 0,
            'status' => 'COMPLETED',
            'uploaded_by' => 'Admin ULP Indramayu Kota',
            'completed_at' => $now->copy()->subDays(4)->setTime(9, 15),
            'created_at' => $now->copy()->subDays(4)->setTime(9, 15),
        ]);

        UploadLog::create([
            'file_type' => 'DAILY_TRANSACTION',
            'file_name' => 'DAILY_TRX_' . $now->copy()->subDays(4)->format('Ymd') . '.xlsx',
            'file_size_bytes' => round(3.2 * 1024 * 1024),
            'thblrek' => $thblrek,
            'id_ulp' => '53403',
            'total_rows' => 3902,
            'success_rows' => 3902,
            'failed_rows' => 0,
            'status' => 'COMPLETED',
            'uploaded_by' => 'Admin ULP Indramayu Kota',
            'completed_at' => $now->copy()->subDays(4)->setTime(8, 30),
            'created_at' => $now->copy()->subDays(4)->setTime(8, 30),
        ]);

        UploadLog::create([
            'file_type' => 'MASTER_DATA',
            'file_name' => 'MASTER_DATA_' . $thblrek . '.xls',
            'file_size_bytes' => round(14.8 * 1024 * 1024),
            'thblrek' => $thblrek,
            'id_ulp' => '53403',
            'total_rows' => 12194,
            'success_rows' => 8500,
            'failed_rows' => 0,
            'status' => 'PROCESSING',
            'uploaded_by' => 'Admin ULP Indramayu Kota',
            'created_at' => $now->copy()->subDays(4)->setTime(8, 10),
        ]);

        UploadLog::create([
            'file_type' => 'DAILY_TRANSACTION',
            'file_name' => 'DAILY_TRX_ERR_FORMAT.xlsx',
            'file_size_bytes' => 94 * 1024,
            'thblrek' => $thblrek,
            'id_ulp' => '53403',
            'total_rows' => 150,
            'success_rows' => 0,
            'failed_rows' => 150,
            'status' => 'FAILED',
            'error_message' => "Gagal parsing header pada baris ke-1. Kolom wajib 'IDPEL' dan 'THBLREK' tidak ditemukan — pastikan file menggunakan template resmi Transaksi Harian (lihat halaman Upload Data).",
            'uploaded_by' => 'Admin ULP Indramayu Kota',
            'completed_at' => $now->copy()->subDays(5)->setTime(17, 0, 18),
            'created_at' => $now->copy()->subDays(5)->setTime(17, 0, 12),
        ]);
    }
}
