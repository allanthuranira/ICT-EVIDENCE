<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            // PC section (A, B1, B2, C, D) — nullable for workplan items
            if (!Schema::hasColumn('performance_items', 'section')) {
                $table->string('section', 20)->nullable()->after('name');
            }

            // Workplan hierarchy fields — nullable for PC items
            if (!Schema::hasColumn('performance_items', 'strategic_issue')) {
                $table->text('strategic_issue')->nullable()->after('section');
            }
            if (!Schema::hasColumn('performance_items', 'strategic_goal')) {
                $table->text('strategic_goal')->nullable()->after('strategic_issue');
            }
            if (!Schema::hasColumn('performance_items', 'kra_group')) {
                $table->string('kra_group', 150)->nullable()->after('strategic_goal');
            }
            if (!Schema::hasColumn('performance_items', 'outcomes')) {
                $table->text('outcomes')->nullable()->after('kra_group');
            }
            if (!Schema::hasColumn('performance_items', 'strategic_objective')) {
                $table->text('strategic_objective')->nullable()->after('outcomes');
            }

            // Cycle / lifecycle fields used by the insert
            if (!Schema::hasColumn('performance_items', 'fy_start_year')) {
                $table->unsignedSmallInteger('fy_start_year')->nullable()->after('q4');
            }
            if (!Schema::hasColumn('performance_items', 'active')) {
                $table->boolean('active')->default(true)->after('fy_start_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            foreach ([
                'section', 'strategic_issue', 'strategic_goal', 'kra_group',
                'outcomes', 'strategic_objective', 'fy_start_year', 'active',
            ] as $col) {
                if (Schema::hasColumn('performance_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};