<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->json('title');
            $table->string('slug')->unique();
            $table->json('content')->nullable();
            $table->json('excerpt')->nullable();
            $table->json('meta_title')->nullable();
            $table->json('meta_description')->nullable();
            $table->string('featured_image')->nullable();
            $table->string('template')->default('default');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_homepage')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
