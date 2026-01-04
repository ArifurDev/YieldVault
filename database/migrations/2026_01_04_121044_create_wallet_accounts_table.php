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
        Schema::create('wallet_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id') // admin or manager
            ->constrained('users')
                ->cascadeOnDelete();

            $table->string('type');
            // bank | mobile

            $table->string('label');
            // City Bank, bKash Personal, Binance BTC

            $table->string('account_number');
            // bank acc / mobile number / wallet address

            $table->string('network')->nullable();
            // BTC / TRC20 / ERC20

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['user_id', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_accounts');
    }
};
