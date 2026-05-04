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
        Schema::create('challenges', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type'); // direct, open
            $table->string('status'); // pending, active, completed, cancelled
            $table->integer('prize_pool')->default(0);
            $table->timestamp('ends_at');
            $table->foreignUuid('winner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('challenge_participations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('wave_id')->constrained()->cascadeOnDelete();
            $table->integer('votes_count')->default(0);
            $table->timestamps();

            $table->unique(['challenge_id', 'user_id']);
        });

        Schema::create('challenge_votes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('participation_id')->constrained('challenge_participations')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['participation_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_votes');
        Schema::dropIfExists('challenge_participations');
        Schema::dropIfExists('challenges');
    }
};
