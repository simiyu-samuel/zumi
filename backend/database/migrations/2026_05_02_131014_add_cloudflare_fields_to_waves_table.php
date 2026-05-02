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
        Schema::table('waves', function (Blueprint $table) {
            $table->string('cloudflare_id')->nullable()->after('user_id');
            $table->string('status')->default('pending')->after('cloudflare_id'); // pending, processing, ready, failed
            $table->json('video_metadata')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waves', function (Blueprint $table) {
            $table->dropColumn(['cloudflare_id', 'status', 'video_metadata']);
        });
    }
};
