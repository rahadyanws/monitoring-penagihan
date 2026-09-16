<?php

namespace App\Http\Controllers;

use App\Models\UploadLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UploadExcelController extends Controller
{
    public function create()
    {
        $throughputBarisPerDetik = 2450; // angka tetap sesuai contoh wireframe (demo)
        $antreanTerpakaiPersen = UploadLog::where('status', 'PROCESSING')->count() > 0 ? 45 : 18;

        return view('upload-excel.create', compact('throughputBarisPerDetik', 'antreanTerpakaiPersen'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'file_type' => 'required|in:MASTER_DATA,DAILY_TRANSACTION,TARGET_MONITORING',
            'thblrek' => 'required|integer',
            'id_ulp' => 'required|string',
            'file' => 'required|file|mimes:xls,xlsx|max:51200', // 50MB
        ]);

        $path = $request->file('file')->store('imports');

        // CATATAN PROTOTYPE: parsing/validasi baris ASLI (rap2hpoutre/fast-excel,
        // ProcessMasterDataJob dkk — lihat AGENTS.md) belum diimplementasikan di sini.
        // Supaya demo tetap meyakinkan, status "PROCESSING" ditutup otomatis jadi
        // "COMPLETED" dengan jumlah baris hasil estimasi dari ukuran file.
        $estimasiBaris = max(10, (int) round($request->file('file')->getSize() / 900));

        $log = UploadLog::create([
            'file_type' => $validated['file_type'],
            'file_name' => $request->file('file')->getClientOriginalName(),
            'file_path' => $path,
            'file_size_bytes' => $request->file('file')->getSize(),
            'thblrek' => $validated['thblrek'],
            'id_ulp' => $validated['id_ulp'],
            'total_rows' => $estimasiBaris,
            'success_rows' => $estimasiBaris,
            'failed_rows' => 0,
            'status' => 'COMPLETED',
            'uploaded_by' => 'Super Admin (Prototype)',
            'completed_at' => Carbon::now(),
        ]);

        return redirect()
            ->route('log-sinkron.index')
            ->with('success', "Berkas \"{$log->file_name}\" berhasil diunggah & diproses ({$estimasiBaris} baris).");
    }
}
