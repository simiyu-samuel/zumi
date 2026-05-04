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
        Schema::create('circle_join_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('circle_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, approved, declined
            $table->timestamps();

            $table->unique(['user_id', 'circle_id', 'status'], 'unique_pending_request')
                ->where('status', 'pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('circle_join_requests');
    }
};
