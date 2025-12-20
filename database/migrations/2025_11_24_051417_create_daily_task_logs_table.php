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
        Schema::create('daily_task_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('daily_task_id')->nullable();
            $table->unsignedBigInteger('task_id')->nullable();
            $table->integer('xp_rewarded')->default(0);
            // For streak date tracking
            $table->date('completed_date')->nullable()->index();
            // Streak snapshot (1,2,3,4,5)
            $table->string('streak')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('daily_task_id')->references('id')->on('daily_tasks')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_task_logs');
    }
};
