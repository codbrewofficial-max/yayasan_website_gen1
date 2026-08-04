<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('label');
            $table->string('utm_source');
            $table->string('utm_medium');
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('short_code')->unique();
            $table->string('target_url');
            $table->unsignedBigInteger('clicks_count')->default(0);
            $table->timestamp('last_clicked_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'campaign_id']);
        });

        Schema::create('link_clicks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('campaign_link_id')->constrained('campaign_links')->cascadeOnDelete();
            $table->string('referrer')->nullable();
            $table->string('device_type')->default('desktop');
            $table->timestamp('clicked_at')->nullable();
            $table->timestamps();

            $table->index(['campaign_link_id', 'clicked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('link_clicks');
        Schema::dropIfExists('campaign_links');
    }
};