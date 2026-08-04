<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_visits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('page_url');
            $table->string('source')->nullable();
            $table->string('device_type')->default('desktop');
            $table->timestamp('visited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'visited_at']);
            $table->index(['tenant_id', 'page_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_visits');
    }
};