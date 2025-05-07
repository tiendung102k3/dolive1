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
        Schema::create("stream_destinations", function (Blueprint $table) {
            $table->id();
            $table->foreignId("livestream_id")->constrained()->onDelete("cascade");
            $table->foreignId("user_id")->constrained()->onDelete("cascade"); // For easier querying by user
            $table->string("platform_name"); // e.g., YouTube, Facebook, Twitch, Custom RTMP
            $table->string("rtmp_url");
            $table->string("stream_key");
            $table->string("status")->default("pending"); // e.g., pending, active, error, completed
            $table->text("platform_stream_id")->nullable(); // ID from the platform if available
            $table->text("error_message")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("stream_destinations");
    }
};
