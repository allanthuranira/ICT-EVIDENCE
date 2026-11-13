<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /*
     * Each PC Target / Workplan KRA mapped to a campus can now be assigned a
     * specific responsible person (recipient) who receives the evidence and
     * quarterly progress report emails for that item. If left empty, the
     * campus's default active representative is used as before.
     */
    public function up(): void
    {
        Schema::table('campus_performance_item', function (Blueprint $table) {
            $table->foreignId('recipient_id')->nullable()->after('performance_item_id')
                ->constrained('recipients')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('campus_performance_item', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recipient_id');
        });
    }
};
