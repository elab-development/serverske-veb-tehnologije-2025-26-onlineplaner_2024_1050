<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('planner_items', function (Blueprint $table) {
            $table->foreign('planner_id')
                ->references('id')
                ->on('planners')
                ->cascadeOnDelete();

            $table->foreign('planner_category_id')
                ->references('id')
                ->on('planner_categories')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planner_items', function (Blueprint $table) {
            $table->dropForeign(['planner_id']);
            $table->dropForeign(['planner_category_id']);
        });
    }
};
