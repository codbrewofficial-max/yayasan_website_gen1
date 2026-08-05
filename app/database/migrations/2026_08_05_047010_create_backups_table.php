<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->string('type'); // database / assets / full
            $table->string('scope'); // tenant / platform
            $table->foreignUuid('triggered_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->string('file_name')->nullable();
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('status')->default('pending');
            $table->string('error_message')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'created_at']);
            $table->index(['scope', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backups');
    }
};
