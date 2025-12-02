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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamp('timestamp');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_name')->nullable();
            $table->string('ip_address');
            $table->text('user_agent')->nullable();
            $table->string('method', 10);
            $table->text('url');
            $table->string('route')->nullable();
            $table->unsignedInteger('status_code');
            $table->decimal('duration_ms', 10, 2);
            $table->unsignedInteger('request_size')->default(0);
            $table->unsignedInteger('response_size')->default(0);
            $table->json('request_params')->nullable();
            $table->json('uploaded_files')->nullable();
            $table->string('event_type')->default('security_audit');
            $table->text('notes')->nullable();

            $table->index(['user_id', 'timestamp']);
            $table->index(['ip_address', 'timestamp']);
            $table->index(['route', 'timestamp']);
            $table->index('timestamp');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
