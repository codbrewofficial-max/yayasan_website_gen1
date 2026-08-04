<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('category')->nullable();
            $table->string('status')->default('ongoing');
            $table->string('location')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignUuid('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
