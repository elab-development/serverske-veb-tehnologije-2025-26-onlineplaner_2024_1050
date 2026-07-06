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
            $table->enum('item_type', ['task', 'event', 'habit', 'note'])->change();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])
                ->default('pending')
                ->change();
            $table->enum('priority', ['low', 'medium', 'high'])
                ->default('medium')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('planner_items', function (Blueprint $table) {
            $table->string('item_type')->change();
            $table->string('status')->change();
            $table->string('priority')->change();
        });
    }
};
