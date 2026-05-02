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
        Schema::create('drops_ledger', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase / gift / earn / spend / payout / fee / refund
            $table->integer('amount'); // Drops — always positive
            $table->enum('direction', ['credit', 'debit']);
            $table->string('reference_type')->nullable(); // Polymorphic: wave / circle / skill_drop / room / challenge / collab
            $table->uuid('reference_id')->nullable(); // UUID of related entity
            $table->enum('status', ['pending', 'completed', 'failed', 'reversed'])->default('completed');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drops_ledger');
    }
};
