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
        Schema::table('clients', function (Blueprint $table) {
            // Add company_id first if it doesn't exist
            if (!Schema::hasColumn('clients', 'company_id')) {
                $table->foreignId('company_id')->nullable()->after('agency_id')->constrained('companies')->onDelete('set null');
            }
            
            // Add new comprehensive client fields if they don't exist
            if (!Schema::hasColumn('clients', 'serial_no')) {
                $table->integer('serial_no')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('clients', 'name_of_client')) {
                $table->string('name_of_client', 191)->nullable()->after('serial_no');
            }
            if (!Schema::hasColumn('clients', 'to_title')) {
                $table->string('to_title', 20)->nullable()->after('name_of_client');
            }
            if (!Schema::hasColumn('clients', 'site_name')) {
                $table->string('site_name', 191)->nullable()->after('to_title');
            }
            if (!Schema::hasColumn('clients', 'address')) {
                $table->text('address')->nullable()->after('site_name');
            }
            if (!Schema::hasColumn('clients', 'dob')) {
                $table->date('dob')->nullable()->after('address');
            }
            if (!Schema::hasColumn('clients', 'date_of_anniversary')) {
                $table->date('date_of_anniversary')->nullable()->after('dob');
            }
            if (!Schema::hasColumn('clients', 'contact_no_1')) {
                $table->string('contact_no_1', 20)->nullable()->after('date_of_anniversary');
            }
            if (!Schema::hasColumn('clients', 'contact_no_2')) {
                $table->string('contact_no_2', 20)->nullable()->after('contact_no_1');
            }
            if (!Schema::hasColumn('clients', 'site_supervisor_contact')) {
                $table->string('site_supervisor_contact', 20)->nullable()->after('contact_no_2');
            }
            if (!Schema::hasColumn('clients', 'site_admin_contact')) {
                $table->string('site_admin_contact', 20)->nullable()->after('site_supervisor_contact');
            }
            if (!Schema::hasColumn('clients', 'site_manager_contact')) {
                $table->string('site_manager_contact', 20)->nullable()->after('site_admin_contact');
            }
            if (!Schema::hasColumn('clients', 'gst_no')) {
                $table->string('gst_no', 30)->nullable()->after('site_manager_contact');
            }
            if (!Schema::hasColumn('clients', 'tds_percentage')) {
                $table->decimal('tds_percentage', 5, 2)->nullable()->after('gst_no');
            }
            if (!Schema::hasColumn('clients', 'pan_no')) {
                $table->string('pan_no', 20)->nullable()->after('tds_percentage');
            }
            if (!Schema::hasColumn('clients', 'primary_email_1')) {
                $table->string('primary_email_1', 191)->nullable()->after('pan_no');
            }
            if (!Schema::hasColumn('clients', 'primary_email_2')) {
                $table->string('primary_email_2', 191)->nullable()->after('primary_email_1');
            }
            if (!Schema::hasColumn('clients', 'additional_charges')) {
                $table->decimal('additional_charges', 10, 2)->nullable()->after('primary_email_2');
            }
            if (!Schema::hasColumn('clients', 'additional_charges_comment')) {
                $table->text('additional_charges_comment')->nullable()->after('additional_charges');
            }
            if (!Schema::hasColumn('clients', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('additional_charges_comment');
            }
            
            // Notification preferences
            if (!Schema::hasColumn('clients', 'sms_reports')) {
                $table->boolean('sms_reports')->default(false)->after('status');
            }
            if (!Schema::hasColumn('clients', 'sms_attendance')) {
                $table->boolean('sms_attendance')->default(false)->after('sms_reports');
            }
            if (!Schema::hasColumn('clients', 'sms_bill')) {
                $table->boolean('sms_bill')->default(false)->after('sms_attendance');
            }
            if (!Schema::hasColumn('clients', 'email_reports')) {
                $table->boolean('email_reports')->default(false)->after('sms_bill');
            }
            if (!Schema::hasColumn('clients', 'email_attendance')) {
                $table->boolean('email_attendance')->default(false)->after('email_reports');
            }
            if (!Schema::hasColumn('clients', 'email_bill')) {
                $table->boolean('email_bill')->default(false)->after('email_attendance');
            }
            if (!Schema::hasColumn('clients', 'email_bill_reminder')) {
                $table->boolean('email_bill_reminder')->default(false)->after('email_bill');
            }
            if (!Schema::hasColumn('clients', 'email_payment_receipt')) {
                $table->boolean('email_payment_receipt')->default(false)->after('email_bill_reminder');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'company_id', 'serial_no', 'name_of_client', 'to_title', 'site_name', 'address',
                'dob', 'date_of_anniversary', 'contact_no_1', 'contact_no_2',
                'site_supervisor_contact', 'site_admin_contact', 'site_manager_contact',
                'gst_no', 'tds_percentage', 'pan_no', 'primary_email_1', 'primary_email_2',
                'additional_charges', 'additional_charges_comment', 'status',
                'sms_reports', 'sms_attendance', 'sms_bill',
                'email_reports', 'email_attendance',
                'email_bill', 'email_bill_reminder', 'email_payment_receipt'
            ]);
        });
    }
};
