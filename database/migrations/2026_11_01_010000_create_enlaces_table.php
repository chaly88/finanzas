<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enlaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('folder_id')->nullable()->constrained('enlaces')->nullOnDelete();
            $table->string('title', 180);
            $table->string('url', 2048);
            $table->text('description')->nullable();
            $table->json('tags')->nullable();
            $table->boolean('is_folder')->default(false);
            $table->boolean('is_favorite')->default(false);
            $table->unsignedInteger('visits')->default(0);
            $table->timestamp('last_visited_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'folder_id', 'is_folder']);
            $table->index(['user_id', 'is_favorite']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enlaces');
    }
};
