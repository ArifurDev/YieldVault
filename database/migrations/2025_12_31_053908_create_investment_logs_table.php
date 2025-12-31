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
        Schema::create('investment_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_investment_id');
            $table->date('date');
            $table->decimal('profit', 15, 2);
            $table->timestamps();

            // Anti-duplicate constraint
            $table->unique(['user_investment_id', 'date']);

            // Foreign key
            $table->foreign('user_investment_id')->references('id')->on('user_investments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_logs');
    }
};
