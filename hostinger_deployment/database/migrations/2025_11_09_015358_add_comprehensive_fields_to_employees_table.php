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
        Schema::table('employees', function (Blueprint $table) {
            // Personal Information Details
            $table->string('name')->nullable(); // Full employee name (separate from first/last)
            $table->string('designation')->nullable();
            $table->string('education')->nullable();
            $table->string('father_name')->nullable();
            $table->string('nationality')->default('Indian');
            $table->text('current_address')->nullable();
            $table->text('permanent_address')->nullable();
            $table->boolean('same_address')->default(false); // Checkbox for "As per above"
            $table->date('date_of_birth')->nullable();
            $table->integer('age')->nullable();
            $table->enum('gender', ['Male', 'Female', 'Other'])->nullable();
            $table->enum('marital_status', ['Single', 'Married', 'Divorced', 'Widowed'])->nullable();
            $table->string('photo_path')->nullable(); // Path to uploaded photo
            
            // Shift Hour
            $table->string('shift_hour')->nullable();
            
            // PF/ESIC Details
            $table->string('pf_no')->nullable();
            $table->string('uan_no')->nullable();
            $table->string('esic')->nullable();
            $table->decimal('esic_percentage', 5, 2)->default(0.75);
            $table->decimal('pf_percentage', 5, 2)->default(12.00);
            $table->boolean('pt_charges_apply')->default(false);
            
            // Bank Account Details
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('account_no')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_phone_no')->nullable();
            $table->string('account_holder_name')->nullable();
            
            // Old Company Details
            $table->string('old_company_name')->nullable();
            $table->string('old_company_year')->nullable();
            $table->text('reason_for_leaving')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'name', 'designation', 'education', 'father_name', 'nationality', 
                'current_address', 'permanent_address', 'same_address',
                'date_of_birth', 'age', 'gender', 'marital_status', 'photo_path',
                'shift_hour', 'pf_no', 'uan_no', 'esic', 'esic_percentage', 
                'pf_percentage', 'pt_charges_apply', 'bank_name', 'bank_branch',
                'account_no', 'ifsc_code', 'bank_phone_no', 'account_holder_name',
                'old_company_name', 'old_company_year', 'reason_for_leaving'
            ]);
        });
    }
};
