<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('circles', function (Blueprint $table) {
            // Monthly price in Drops for Premium/FreeGated circles. Null = free to join.
            $table->unsignedInteger('monthly_drops_price')->nullable()->after('members_count');
        });
    }

    public function down(): void
    {
        Schema::table('circles', function (Blueprint $table) {
            $table->dropColumn('monthly_drops_price');
        });
    }
};
