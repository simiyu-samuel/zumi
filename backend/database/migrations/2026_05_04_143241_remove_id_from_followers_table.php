<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The `followers` table is a pure pivot table — the composite key
 * (follower_id, following_id) is sufficient as a natural key.
 * Removing the redundant surrogate UUID `id` column keeps the schema clean
 * and avoids manual UUID generation in raw `attach()` calls.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->dropColumn('id');
        });
    }

    public function down(): void
    {
        Schema::table('followers', function (Blueprint $table) {
            $table->uuid('id')->primary()->first();
        });
    }
};
