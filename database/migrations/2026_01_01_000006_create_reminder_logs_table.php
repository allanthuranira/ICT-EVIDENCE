<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->unsignedSmallInteger('fy_start_year');
            $table->enum('kind', ['evidence', 'report']);
            $table->string('sent_to');
            $table->string('cc')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            // do not send the exact same reminder twice
            $table->unique(['performance_item_id', 'campus_id', 'quarter', 'fy_start_year', 'kind', 'sent_at'], 'reminder_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_logs');
    }
};
