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
        Schema::create('planner_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planner_id');
            $table->unsignedBigInteger('planner_category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('item_type');
            $table->string('status');
            $table->string('priority');
            $table->date('due_date')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('position')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('planner_items');
    }
};
