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
        if (! Schema::hasTable('employee_identity_proofs')) {
            Schema::create('employee_identity_proofs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('identity_proof_type');
                $table->string('identity_proof_no');
                $table->string('image_path')->nullable();
                $table->string('tenant_uuid');
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['employee_id', 'tenant_uuid']);
            });
        } else {
            Schema::table('employee_identity_proofs', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_identity_proofs', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable();
                }
                if (! Schema::hasColumn('employee_identity_proofs', 'identity_proof_type')) {
                    $table->string('identity_proof_type')->nullable();
                }
                if (! Schema::hasColumn('employee_identity_proofs', 'identity_proof_no')) {
                    $table->string('identity_proof_no')->nullable();
                }
                if (! Schema::hasColumn('employee_identity_proofs', 'image_path')) {
                    $table->string('image_path')->nullable();
                }
                if (! Schema::hasColumn('employee_identity_proofs', 'tenant_uuid')) {
                    $table->string('tenant_uuid')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_identity_proofs');
    }
};
