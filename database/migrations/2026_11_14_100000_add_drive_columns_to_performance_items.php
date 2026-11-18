// database/migrations/2026_11_14_100000_add_drive_columns_to_performance_items.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            if (!Schema::hasColumn('performance_items', 'drive_folder_id')) {
                $table->string('drive_folder_id')->nullable()->after('active');
            }
            if (!Schema::hasColumn('performance_items', 'drive_folder_url')) {
                $table->string('drive_folder_url', 500)->nullable()->after('drive_folder_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            foreach (['drive_folder_id', 'drive_folder_url'] as $c) {
                if (Schema::hasColumn('performance_items', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};