<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quarterly_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->unsignedSmallInteger('fy_start_year');
            $table->decimal('quarter_target', 8, 2)->nullable();
            $table->decimal('quarter_actual', 8, 2)->nullable();
            $table->decimal('quarter_variance', 8, 2)->nullable();
            $table->text('quarter_remarks')->nullable();         // incl. mitigation strategies
            $table->decimal('cumulative_target', 8, 2)->nullable();
            $table->decimal('cumulative_actual', 8, 2)->nullable();
            $table->decimal('cumulative_variance', 8, 2)->nullable();
            $table->text('cumulative_remarks')->nullable();
            $table->enum('status', ['done', 'initiated', 'evidence_available', 'not_done', 'not_due'])->default('not_done');
            $table->string('submitted_by_email');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['performance_item_id', 'campus_id', 'quarter', 'fy_start_year'], 'qreport_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quarterly_reports');
    }
};
