<?php

use App\Enum\Wallet\TransactionStatus;
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
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_user_id')->nullable()
                ->references('id')->on('users')->nullOnDelete();

            $table->foreignId('to_user_id')->nullable()
                ->references('id')->on('users')->nullOnDelete();

            $table->decimal('amount', 15, 2);

            $table->string('type');
            $table->string('source');
            $table->string('status')->default(TransactionStatus::PENDING->value);

            $table->unsignedBigInteger('reference_id')->nullable(); // deposit / withdraw id

            $table->timestamps();

            $table->index(['from_user_id', 'to_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};
