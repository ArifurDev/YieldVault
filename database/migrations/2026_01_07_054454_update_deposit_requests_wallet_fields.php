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
        Schema::table('deposit_requests', function (Blueprint $table) {
            // Relation (future logic)
            $table->foreignId('manager_wallet_account_id')
                ->nullable()
                ->after('manager_id')
                ->constrained('wallet_accounts')
                ->nullOnDelete();

            // Snapshot fields (history safe)
            $table->string('wallet_method')->nullable()->after('amount');
            $table->string('wallet_label')->nullable()->after('wallet_method');
            $table->string('wallet_account_number')->nullable()->after('wallet_label');
            $table->string('wallet_provider')->nullable()->after('wallet_account_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deposit_requests', function (Blueprint $table) {
            $table->dropForeign(['manager_wallet_account_id']);
            $table->dropColumn([
                'manager_wallet_account_id',
                'wallet_method',
                'wallet_label',
                'wallet_account_number',
                'wallet_provider',
            ]);
        });
    }
};
