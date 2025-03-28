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
        Schema::dropIfExists('post_meta');
        Schema::dropIfExists('post_metas');
        
        Schema::create('post_metas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('meta_key');
            $table->text('meta_value')->nullable();
            $table->timestamps();

            // Add a unique constraint to prevent duplicate meta keys for a post
            $table->unique(['post_id', 'meta_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_metas');
    }
};