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
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // Level 1 Bronze
            $table->string('slug')->unique();       // bronze, silver
            $table->integer('required_xp');         // 200, 400, 1200
            $table->decimal('daily_roi', 5, 2);     // 0.5, 1.2, 3.5
            $table->string('icon')->nullable();     // icon path
            $table->integer('order')->default(1);  // UI sorting
            $table->boolean('is_active')->default(true);
            $table->boolean('is_coming_soon')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('levels');
    }
};
