<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeRecruitmentFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'client_id')) {
                $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            }

            if (! Schema::hasColumn('employees', 'job_role')) {
                $table->string('job_role')->nullable();
            }

            if (! Schema::hasColumn('employees', 'shift')) {
                // store simple shift descriptor or JSON string
                $table->text('shift')->nullable();
            }

            if (! Schema::hasColumn('employees', 'kyc_status')) {
                $table->string('kyc_status')->default('pending');
            }

            if (! Schema::hasColumn('employees', 'aadhar_path')) {
                $table->string('aadhar_path')->nullable();
            }

            if (! Schema::hasColumn('employees', 'pan_path')) {
                $table->string('pan_path')->nullable();
            }

            if (! Schema::hasColumn('employees', 'police_verification_path')) {
                $table->string('police_verification_path')->nullable();
            }

            if (! Schema::hasColumn('employees', 'kyc_completed_at')) {
                $table->timestamp('kyc_completed_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'client_id')) {
                $table->dropConstrainedForeignId('client_id');
            }

            foreach (['job_role','shift','kyc_status','aadhar_path','pan_path','police_verification_path','kyc_completed_at'] as $col) {
                if (Schema::hasColumn('employees', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
