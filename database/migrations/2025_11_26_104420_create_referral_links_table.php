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
        Schema::create('referral_links', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id'); // who created the link
            $table->string('ref_code', 20);
            $table->string('unique_token', 64)->unique();
            $table->dateTime('expires_at');
            $table->text('meta')->nullable(); // device, browser, IP etc. optional
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_links');
    }
};
