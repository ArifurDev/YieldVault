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
        // deposit_requests
        Schema::table('deposit_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('deposit_requests', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('status');
            }
        });

        // withdraw_requests
        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraw_requests', 'reject_reason')) {
                $table->string('reject_reason')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            if (Schema::hasColumn('deposit_requests', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
        });

        Schema::table('withdraw_requests', function (Blueprint $table) {
            if (Schema::hasColumn('withdraw_requests', 'reject_reason')) {
                $table->dropColumn('reject_reason');
            }
        });
    }
};
