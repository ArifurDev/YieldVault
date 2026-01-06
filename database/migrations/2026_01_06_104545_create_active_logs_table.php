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
        Schema::create('active_logs', function (Blueprint $table) {
            $table->id();
            // Who did the action
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();

            // Who is affected (optional)
            $table->foreignId('target_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Enums
            $table->string('type');   // deposit | withdraw | kyc
            $table->string('action'); // approved | rejected | paid | flagged
            $table->string('level');  // success | danger | warning | info

            $table->decimal('amount', 15, 2)->nullable();

            // Polymorphic reference
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->string('message');

            $table->timestamps();

            $table->index(['type', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('active_logs');
    }
};
