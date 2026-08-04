<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignUuid('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_id')->unique();
            $table->string('donor_name');
            $table->string('donor_email');
            $table->string('donor_phone');
            $table->boolean('is_anonymous')->default(false);
            $table->decimal('amount', 15, 2);
            $table->text('message')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('pending');
            $table->string('payment_gateway_ref')->nullable();
            $table->string('donation_type')->default('one_time');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->uuid('campaign_link_id')->nullable();
            $table->uuid('page_visit_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'campaign_id', 'payment_status']);
            $table->index(['tenant_id', 'payment_gateway_ref']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
