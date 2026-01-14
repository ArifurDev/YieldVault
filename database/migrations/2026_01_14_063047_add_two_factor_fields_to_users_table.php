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
            $table->boolean('two_factor_enabled')->default(false)->after('password');
            $table->string('two_factor_email')->nullable()->after('email');
            $table->timestamp('two_factor_verified_at')->nullable()->after('two_factor_email');
            $table->unsignedTinyInteger('failed_2fa_attempts')->default(0)->after('two_factor_verified_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('two_factor_enabled');
            $table->dropColumn('two_factor_email');
            $table->dropColumn('two_factor_verified_at');
            $table->dropColumn('failed_2fa_attempts');
        });
    }
};
