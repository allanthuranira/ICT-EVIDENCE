<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /*
     * Template groupings captured when a PC Target / Workplan KRA is first
     * input (and editable later). They drive the layout of the official
     * quarterly reporting Word documents:
     *   - "section" groups PC rows (e.g. "A. Financial Stewardship and Fiscal Discipline")
     *   - the strategic hierarchy fields create the heading rows of the
     *     Annual Work Plan report (Strategic Issue / Strategic goal /
     *     Key Result Area / Outcomes / Strategic Objective)
     * All are nullable: some workplans do not have all of these levels.
     */
    public function up(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            $table->string('section')->nullable()->after('description');
            $table->string('strategic_issue')->nullable()->after('section');
            $table->string('strategic_goal')->nullable()->after('strategic_issue');
            $table->string('kra_group')->nullable()->after('strategic_goal');
            $table->text('outcomes')->nullable()->after('kra_group');
            $table->string('strategic_objective')->nullable()->after('outcomes');
        });
    }

    public function down(): void
    {
        Schema::table('performance_items', function (Blueprint $table) {
            $table->dropColumn(['section', 'strategic_issue', 'strategic_goal', 'kra_group', 'outcomes', 'strategic_objective']);
        });
    }
};
