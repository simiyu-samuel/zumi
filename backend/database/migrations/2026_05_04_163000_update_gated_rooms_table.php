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
        Schema::table('gated_rooms', function (Blueprint $table) {
            $table->string('livekit_room_name')->nullable()->after('title');
            $table->boolean('is_replay_enabled')->default(false)->after('status');
            $table->string('replay_url')->nullable()->after('is_replay_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gated_rooms', function (Blueprint $table) {
            $table->dropColumn(['livekit_room_name', 'is_replay_enabled', 'replay_url']);
        });
    }
};
