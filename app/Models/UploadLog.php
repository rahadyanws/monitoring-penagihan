<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadLog extends Model
{
    protected $table = 'upload_log';
    protected $primaryKey = 'id_upload';
    protected $fillable = [
        'file_type', 'file_name', 'file_path', 'file_size_bytes', 'thblrek', 'id_ulp',
        'total_rows', 'success_rows', 'failed_rows', 'status', 'error_message',
        'uploaded_by', 'completed_at',
    ];
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function progressPercent(): int
    {
        return $this->total_rows > 0
            ? (int) round(($this->success_rows / $this->total_rows) * 100)
            : 0;
    }
}
