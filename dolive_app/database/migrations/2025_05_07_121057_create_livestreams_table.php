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
        Schema::create("livestreams", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->foreignId("video_id")->constrained()->onDelete("cascade");
            $table->string("title")->nullable(); // Optional, can be derived from video
            $table->string("status")->default("pending"); // e.g., pending, scheduled, starting, streaming, completed, failed, stopped
            $table->timestamp("scheduled_at")->nullable();
            $table->timestamp("started_at")->nullable();
            $table->timestamp("ended_at")->nullable();
            $table->text("error_message")->nullable();
            $table->string("stream_identifier")->unique()->nullable(); // Unique ID for Node.js service to identify this stream job
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("livestreams");
    }
};
