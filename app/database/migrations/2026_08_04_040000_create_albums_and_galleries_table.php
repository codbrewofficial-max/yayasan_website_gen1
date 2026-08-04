<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('albums', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('views_count')->default(0);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status', 'published_at']);
        });

        Schema::create('galleries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('album_id')->constrained('albums')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->foreignUuid('image_id')->constrained('media')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'album_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('galleries');
        Schema::dropIfExists('albums');
    }
};
