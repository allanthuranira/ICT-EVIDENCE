<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('performance_items', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['pc', 'workplan']);        // PC target or Workplan KRA
            $table->string('code');                          // B1.1, KRA 1.1, D1.4 ...
            $table->string('name');                          // short title
            $table->text('description')->nullable();         // full description / commitments
            $table->string('unit_of_measure')->nullable();   // %, No., Report ...
            $table->decimal('annual_target', 8, 2)->nullable();
            $table->boolean('q1')->default(false);           // applicable in quarter 1
            $table->boolean('q2')->default(false);
            $table->boolean('q3')->default(false);
            $table->boolean('q4')->default(false);
            $table->unsignedSmallInteger('fy_start_year')->default(2026); // FY 2026/2027
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'code', 'fy_start_year']);
        });

        // Which campus/HQ is responsible for each item
        Schema::create('campus_performance_item', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->constrained()->cascadeOnDelete();
            $table->foreignId('performance_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campus_id', 'performance_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campus_performance_item');
        Schema::dropIfExists('performance_items');
    }
};
