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
        Schema::create('payment_reports', function (Blueprint $table) {
            $table->id();
            // Core relations
            $table->foreignId('ticket_id')->constrained('support_tickets') ->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            // Optional transaction reference
            $table->foreignId('transaction_id')->nullable()->constrained('wallet_transactions')->nullOnDelete();

            // Report meta
            $table->string('category');        // deposit / withdrawal
            $table->string('issue_type');      // delay / failed / mismatch
            $table->text('message');

            // Workflow
            $table->string('status');// open | in_progress | resolved | rejected
            // Audit
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();

            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['status']);
            $table->index(['manager_id']);
            $table->index(['transaction_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_reports');
    }
};
