<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->string('gst_number')->nullable()->after('details');
                $table->string('pan_number')->nullable()->after('gst_number');
                $table->string('email')->nullable()->after('pan_number');
                $table->string('phone')->nullable()->after('email');
                $table->text('registered_address')->nullable()->after('phone');
                $table->text('communication_address')->nullable()->after('registered_address');
                $table->string('company_type')->nullable()->after('communication_address');
                $table->string('crn_number')->nullable()->after('company_type');

                // Contact person details
                $table->string('contact_person_name')->nullable()->after('crn_number');
                $table->string('contact_person_email')->nullable()->after('contact_person_name');
                $table->string('contact_person_phone')->nullable()->after('contact_person_email');
                $table->string('contact_person_designation')->nullable()->after('contact_person_phone');

                // Agency login password (to be used with company email for login)
                $table->string('password')->nullable()->after('contact_person_designation');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('agencies')) {
            Schema::table('agencies', function (Blueprint $table) {
                $table->dropColumn([
                    'gst_number',
                    'pan_number',
                    'email',
                    'phone',
                    'registered_address',
                    'communication_address',
                    'company_type',
                    'crn_number',
                    'contact_person_name',
                    'contact_person_email',
                    'contact_person_phone',
                    'contact_person_designation',
                    'password',
                ]);
            });
        }
    }
};
