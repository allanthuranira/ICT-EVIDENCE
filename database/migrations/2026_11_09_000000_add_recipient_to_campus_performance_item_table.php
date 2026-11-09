<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds a nullable recipient_id to the item <-> campus mapping table
     * so each (item, campus) pair can be routed to a specific reporting officer.
     *
     * Run: php artisan migrate
     */
    public function up(): void
    {
        Schema::table('campus_performance_item', function (Blueprint $table) {
            // Nullable so existing rows remain valid; set per assignment in the dashboard.
            $table->foreignId('recipient_id')
                  ->nullable()
                  ->after('campus_id') // adjust/remove if column order doesn't matter
                  ->constrained('recipients') // assumes a `recipients` table exists
                  ->nullOnDelete();

            // Optional: prevent duplicate recipient rows for the same item+campus assignment.
            // Uncomment only if you don't already have a unique index on (item_id, campus_id).
            // $table->unique(['item_id', 'campus_id']);
        });
    }

    public function down(): void
    {
        Schema::table('campus_performance_item', function (Blueprint $table) {
            // Drop FK first, then the column (order matters in MySQL/Postgres).
            $table->dropConstrainedForeignId('recipient_id');
        });
    }
};