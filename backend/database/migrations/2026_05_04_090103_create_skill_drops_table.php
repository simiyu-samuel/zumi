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
        Schema::create('skill_drops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->integer('price_drops')->default(0);
            $table->string('preview_url')->nullable(); // Cloudflare Stream preview
            $table->string('content_url')->nullable(); // The actual gated content
            $table->integer('sales_count')->default(0);
            $table->integer('rating_avg')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('skill_drop_purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('skill_drop_id')->constrained('skill_drops')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount_paid')->default(0);
            $table->timestamp('purchased_at')->useCurrent();
            $table->timestamps();

            $table->unique(['skill_drop_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skill_drop_purchases');
        Schema::dropIfExists('skill_drops');
    }
};
