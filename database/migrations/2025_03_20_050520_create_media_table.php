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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('file_name');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension');
            $table->bigInteger('size');
            $table->string('path');
            $table->string('disk')->default('public');
            $table->string('title')->nullable();
            $table->text('alt_text')->nullable();
            $table->text('caption')->nullable();
            $table->text('description')->nullable();
            $table->json('meta_data')->nullable(); // For image dimensions, video duration, etc.
            $table->json('responsive_images')->nullable(); // For storing various image sizes
            $table->string('status')->default('ready');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
