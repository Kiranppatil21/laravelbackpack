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
        if (! Schema::hasTable('employee_family_members')) {
            Schema::create('employee_family_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('employee_id');
                $table->string('relationship');
                $table->string('name');
                $table->integer('age');
                $table->string('phone_no')->nullable();
                $table->boolean('is_nominee')->default(false);
                $table->string('tenant_uuid');
                $table->timestamps();

                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
                $table->index(['employee_id', 'tenant_uuid']);
            });
        } else {
            Schema::table('employee_family_members', function (Blueprint $table) {
                if (! Schema::hasColumn('employee_family_members', 'employee_id')) {
                    $table->unsignedBigInteger('employee_id')->nullable();
                }
                if (! Schema::hasColumn('employee_family_members', 'relationship')) {
                    $table->string('relationship')->nullable();
                }
                if (! Schema::hasColumn('employee_family_members', 'name')) {
                    $table->string('name')->nullable();
                }
                if (! Schema::hasColumn('employee_family_members', 'age')) {
                    $table->integer('age')->nullable();
                }
                if (! Schema::hasColumn('employee_family_members', 'phone_no')) {
                    $table->string('phone_no')->nullable();
                }
                if (! Schema::hasColumn('employee_family_members', 'is_nominee')) {
                    $table->boolean('is_nominee')->default(false);
                }
                if (! Schema::hasColumn('employee_family_members', 'tenant_uuid')) {
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
        Schema::dropIfExists('employee_family_members');
    }
};
