<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('evidence_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter');        // 1..4
            $table->unsignedSmallInteger('fy_start_year');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('disk_path');                   // local storage path
            $table->string('drive_file_id')->nullable();   // Google Drive file id
            $table->string('drive_link')->nullable();
            $table->text('notes')->nullable();
            $table->string('submitted_by_email');
            $table->timestamp('submitted_at');
            $table->timestamps();

            // one evidence set per item/campus/quarter
            $table->unique(['performance_item_id', 'campus_id', 'quarter', 'fy_start_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_submissions');
    }
};
