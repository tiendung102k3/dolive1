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
        Schema::create("videos", function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")->constrained()->onDelete("cascade");
            $table->string("title");
            $table->text("description")->nullable();
            $table->string("file_path"); // Relative path in storage
            $table->string("original_filename");
            $table->string("mime_type")->nullable();
            $table->unsignedBigInteger("size")->nullable(); // in bytes
            $table->integer("duration")->nullable(); // in seconds
            $table->string("status")->default("pending"); // e.g., pending, ready, processing, failed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("videos");
    }
};
