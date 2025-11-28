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
        Schema::table('users', function (Blueprint $table) {
            // Each user has a unique referral code
            $table->string('ref_code')
                ->unique()
                ->nullable()
                ->after('id');

            // Which user referred this user
            $table->unsignedBigInteger('ref_id')
                ->nullable()
                ->after('ref_code');

            // Index for fast searching "who used my code"
            $table->index('ref_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['ref_id']);
            $table->dropColumn(['ref_code', 'ref_id']);
        });
    }
};
