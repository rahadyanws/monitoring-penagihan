<?php

namespace App\Http\Controllers;

use App\Models\UploadLog;
use Illuminate\Http\Request;

class LogSinkronController extends Controller
{
    public function index(Request $request)
    {
        $query = UploadLog::query()->orderByDesc('created_at');

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->file_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('file_name', 'like', '%' . $request->q . '%');
        }

        $logs = $query->paginate(10)->withQueryString();

        $statusWorker = UploadLog::where('status', 'PROCESSING')->exists() ? 'AKTIF (2 Daemon Berjalan)' : 'AKTIF (Idle)';
        $antrean = UploadLog::where('status', 'PROCESSING')->count();
        $sedangDiproses = UploadLog::where('status', 'PROCESSING')->first();

        return view('log-sinkron.index', compact('logs', 'statusWorker', 'antrean', 'sedangDiproses'));
    }

    public function show(UploadLog $log)
    {
        return response()->json([
            'id_upload' => $log->id_upload,
            'file_name' => $log->file_name,
            'file_type' => $log->file_type,
            'status' => $log->status,
            'uploaded_by' => $log->uploaded_by,
            'created_at' => optional($log->created_at)->format('d/m/Y H:i:s'),
            'completed_at' => optional($log->completed_at)->format('d/m/Y H:i:s'),
            'total_rows' => $log->total_rows,
            'success_rows' => $log->success_rows,
            'failed_rows' => $log->failed_rows,
            'error_message' => $log->error_message,
        ]);
    }
}
