<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Visitor pre-approvals and invitations
        if (! Schema::hasTable('visitor_invitations')) {
        Schema::create('visitor_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('invitation_code')->unique();
            $table->string('visitor_name');
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_company')->nullable();
            $table->unsignedBigInteger('host_id');
            $table->unsignedBigInteger('invited_by');
            $table->string('purpose');
            $table->timestamp('valid_from');
            $table->timestamp('valid_until');
            $table->enum('status', ['pending', 'used', 'expired', 'cancelled'])->default('pending');
            $table->json('access_areas')->nullable(); // allowed areas/floors
            $table->text('special_instructions')->nullable();
            $table->boolean('escort_required')->default(false);
            $table->json('required_documents')->nullable(); // ID, NDA, etc.
            $table->timestamp('used_at')->nullable();
            $table->unsignedBigInteger('visit_log_id')->nullable();
            $table->timestamps();
            
            $table->foreign('host_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('invited_by')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('visit_log_id')->references('id')->on('visit_logs')->nullOnDelete();
            
            $table->index(['status', 'valid_from', 'valid_until']);
            $table->index(['host_id', 'valid_from']);
        });
        }

        // Visitor watchlist and security alerts
        if (! Schema::hasTable('visitor_watchlist')) {
        Schema::create('visitor_watchlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->string('visitor_name'); // Store name even if visitor record is deleted
            $table->string('visitor_email')->nullable();
            $table->string('visitor_phone')->nullable();
            $table->string('visitor_id_value')->nullable(); // ID number
            $table->enum('threat_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('reason', ['security_incident', 'theft', 'harassment', 'trespassing', 'other'])->default('other');
            $table->text('description');
            $table->unsignedBigInteger('added_by');
            $table->boolean('alert_on_entry')->default(true);
            $table->boolean('auto_deny')->default(false);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('visitor_id')->references('id')->on('visitors')->nullOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->cascadeOnDelete();
            
            $table->index(['is_active', 'expires_at']);
            $table->index(['visitor_id', 'is_active']);
        });
        }

        // Security alerts and incidents
        if (! Schema::hasTable('security_alerts')) {
        Schema::create('security_alerts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['watchlist_entry', 'overstay', 'unauthorized_area', 'tailgating', 'lost_badge', 'emergency', 'other'])->default('other');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('description');
            $table->unsignedBigInteger('visitor_id')->nullable();
            $table->unsignedBigInteger('visit_log_id')->nullable();
            $table->unsignedBigInteger('triggered_by')->nullable(); // user or system
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'false_alarm'])->default('open');
            $table->timestamp('occurred_at');
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_notes')->nullable();
            $table->json('metadata')->nullable(); // device info, location, etc.
            $table->timestamps();
            
            $table->foreign('visitor_id')->references('id')->on('visitors')->nullOnDelete();
            $table->foreign('visit_log_id')->references('id')->on('visit_logs')->nullOnDelete();
            $table->foreign('triggered_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['type', 'severity', 'status']);
            $table->index(['occurred_at', 'status']);
        });
        }

        // Device registry for IoT integration
        if (! Schema::hasTable('visitor_devices')) {
        Schema::create('visitor_devices', function (Blueprint $table) {
            $table->id();
            $table->string('device_id')->unique();
            $table->string('device_name');
            $table->enum('device_type', ['kiosk', 'rfid_reader', 'biometric_scanner', 'thermal_camera', 'qr_scanner', 'tablet', 'other'])->default('kiosk');
            $table->string('location'); // Building A Entrance, Floor 2 Exit, etc.
            $table->string('ip_address')->nullable();
            $table->string('mac_address')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'error'])->default('active');
            $table->json('capabilities')->nullable(); // photo, biometric, temperature, etc.
            $table->json('configuration')->nullable(); // device-specific settings
            $table->timestamp('last_heartbeat')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('managed_by')->nullable();
            $table->timestamps();
            
            $table->foreign('managed_by')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['device_type', 'status']);
            $table->index(['location', 'status']);
        });
        }

        // Visitor feedback and ratings
        if (! Schema::hasTable('visitor_feedback')) {
        Schema::create('visitor_feedback', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('visitor_id');
            $table->unsignedBigInteger('visit_log_id');
            $table->unsignedBigInteger('host_id')->nullable();
            $table->enum('feedback_type', ['visitor_experience', 'facility_rating', 'security_process', 'host_interaction', 'general'])->default('visitor_experience');
            $table->integer('rating')->nullable(); // 1-5 stars
            $table->text('comments')->nullable();
            $table->json('responses')->nullable(); // structured survey responses
            $table->boolean('anonymous')->default(false);
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->foreign('visitor_id')->references('id')->on('visitors')->cascadeOnDelete();
            $table->foreign('visit_log_id')->references('id')->on('visit_logs')->cascadeOnDelete();
            $table->foreign('host_id')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['feedback_type', 'rating']);
            $table->index(['visitor_id', 'created_at']);
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