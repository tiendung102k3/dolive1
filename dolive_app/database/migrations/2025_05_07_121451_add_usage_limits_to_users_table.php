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
        Schema::table("users", function (Blueprint $table) {
            $table->integer("max_concurrent_streams")->default(1)->after("is_admin");
            $table->integer("max_monthly_streaming_minutes")->default(600)->after("max_concurrent_streams"); // e.g., 10 hours
            $table->integer("current_monthly_streaming_minutes")->default(0)->after("max_monthly_streaming_minutes");
            $table->integer("max_destinations_per_stream")->default(2)->after("current_monthly_streaming_minutes");
            // Add a field to reset monthly minutes, or handle this via a scheduled task
            $table->timestamp("streaming_minutes_reset_at")->nullable()->after("max_destinations_per_stream");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            $table->dropColumn([
                "max_concurrent_streams",
                "max_monthly_streaming_minutes",
                "current_monthly_streaming_minutes",
                "max_destinations_per_stream",
                "streaming_minutes_reset_at",
            ]);
        });
    }
};
