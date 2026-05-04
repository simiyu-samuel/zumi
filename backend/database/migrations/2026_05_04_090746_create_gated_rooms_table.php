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
        Schema::create('gated_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('entry_fee_drops')->default(0);
            $table->string('status')->default('scheduled'); // scheduled, live, ended
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('gated_room_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('gated_room_id')->constrained('gated_rooms')->onDelete('cascade');
            $table->foreignUuid('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount_paid')->default(0);
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['gated_room_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gated_room_participants');
        Schema::dropIfExists('gated_rooms');
    }
};
