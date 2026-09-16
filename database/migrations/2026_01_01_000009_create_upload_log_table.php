<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('upload_log', function (Blueprint $table) {
            $table->id('id_upload');
            $table->string('file_type', 50); // MASTER_DATA | DAILY_TRANSACTION | TARGET_MONITORING
            $table->string('file_name', 255);
            $table->string('file_path', 255)->nullable();
            $table->unsignedBigInteger('file_size_bytes')->default(0);
            $table->integer('thblrek')->nullable();
            $table->string('id_ulp', 10)->nullable();
            $table->integer('total_rows')->default(0);
            $table->integer('success_rows')->default(0);
            $table->integer('failed_rows')->default(0);
            $table->string('status', 30)->default('PENDING'); // PENDING|PROCESSING|COMPLETED|FAILED
            $table->text('error_message')->nullable();
            $table->string('uploaded_by', 100)->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('upload_log');
    }
};
