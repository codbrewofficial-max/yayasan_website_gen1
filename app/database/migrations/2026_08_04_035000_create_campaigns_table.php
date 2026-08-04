<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->longText('story')->nullable();
            $table->decimal('target_amount', 15, 2)->nullable();
            $table->decimal('collected_amount', 15, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft');
            $table->foreignUuid('featured_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('donation_type')->default('one_time');
            $table->boolean('show_donor_list')->default(true);
            $table->boolean('allow_anonymous')->default(true);
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->foreignUuid('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignUuid('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
            $table->index(['tenant_id', 'status', 'start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
