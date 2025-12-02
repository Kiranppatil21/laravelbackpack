<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create visitor invitations table
        if (!Schema::hasTable('visitor_invitations')) {
            Schema::create('visitor_invitations', function (Blueprint $table) {
                $table->id();
                $table->string('invitation_code')->unique();
                $table->string('visitor_name');
                $table->string('visitor_email')->nullable();
                $table->string('visitor_phone');
                $table->string('visitor_company')->nullable();
                $table->string('purpose_of_visit');
                $table->unsignedBigInteger('host_employee_id')->nullable();
                $table->unsignedBigInteger('invited_by_id');
                $table->timestamp('scheduled_date');
                $table->timestamp('valid_until');
                $table->enum('status', ['pending', 'accepted', 'expired', 'cancelled'])->default('pending');
                $table->boolean('requires_approval')->default(false);
                $table->text('special_instructions')->nullable();
                $table->timestamp('accepted_at')->nullable();
                $table->timestamps();
                
                $table->index(['status', 'valid_until']);
                $table->index(['visitor_phone', 'visitor_email']);
                $table->index(['scheduled_date']);
            });
        }

        // Create visitor watchlist table
        if (!Schema::hasTable('visitor_watchlist')) {
            Schema::create('visitor_watchlist', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('id_number')->nullable();
                $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->text('reason');
                $table->unsignedBigInteger('added_by_id');
                $table->timestamp('valid_until')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('additional_info')->nullable();
                $table->timestamps();
                
                $table->index(['phone', 'email', 'id_number']);
                $table->index(['risk_level', 'is_active']);
                $table->index(['valid_until']);
            });
        }

        // Create security alerts table
        if (!Schema::hasTable('security_alerts')) {
            Schema::create('security_alerts', function (Blueprint $table) {
                $table->id();
                $table->enum('alert_type', ['watchlist_match', 'background_check_failed', 'suspicious_behavior', 'unauthorized_access', 'other']);
                $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
                $table->string('title');
                $table->text('description');
                $table->unsignedBigInteger('visitor_id')->nullable();
                $table->unsignedBigInteger('triggered_by_id')->nullable();
                $table->enum('status', ['open', 'investigating', 'resolved', 'false_positive'])->default('open');
                $table->unsignedBigInteger('assigned_to_id')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('resolution_notes')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
                
                $table->index(['alert_type', 'severity', 'status']);
                $table->index(['visitor_id']);
                $table->index(['created_at']);
            });
        }

        // Create visitor devices table
        if (!Schema::hasTable('visitor_devices')) {
            Schema::create('visitor_devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_identifier')->unique();
                $table->enum('device_type', ['mobile', 'tablet', 'kiosk', 'rfid_reader', 'thermal_camera', 'biometric_scanner']);
                $table->string('device_name');
                $table->string('location')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->string('push_token')->nullable();
                $table->string('app_version')->nullable();
                $table->string('os_version')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_heartbeat')->nullable();
                $table->json('capabilities')->nullable();
                $table->json('configuration')->nullable();
                $table->timestamps();
                
                $table->index(['device_type', 'is_active']);
                $table->index(['last_heartbeat']);
                $table->index(['user_id']);
            });
        }

        // Create visitor feedback table
        if (!Schema::hasTable('visitor_feedback')) {
            Schema::create('visitor_feedback', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('visitor_id');
                $table->unsignedBigInteger('visit_log_id')->nullable();
                $table->integer('rating')->nullable(); // 1-5 stars
                $table->text('feedback')->nullable();
                $table->json('survey_responses')->nullable();
                $table->enum('feedback_type', ['visit_experience', 'system_usage', 'security_concern', 'suggestion'])->default('visit_experience');
                $table->boolean('is_anonymous')->default(false);
                $table->timestamps();
                
                $table->index(['visitor_id']);
                $table->index(['rating']);
                $table->index(['feedback_type']);
                $table->index(['created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_feedback');
        Schema::dropIfExists('visitor_devices');
        Schema::dropIfExists('security_alerts');
        Schema::dropIfExists('visitor_watchlist');
        Schema::dropIfExists('visitor_invitations');
    }
};