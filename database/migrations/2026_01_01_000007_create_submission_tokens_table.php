<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('submission_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('quarter');
            $table->unsignedSmallInteger('fy_start_year');
            $table->enum('kind', ['evidence', 'report']);
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['performance_item_id', 'campus_id', 'quarter', 'fy_start_year', 'kind'], 'subtoken_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submission_tokens');
    }
};
