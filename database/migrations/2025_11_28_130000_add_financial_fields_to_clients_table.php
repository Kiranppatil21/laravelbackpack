<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (! Schema::hasColumn('clients', 'billing_rate')) {
                    $table->decimal('billing_rate', 10, 2)->nullable()->after('additional_charges');
                }
                if (! Schema::hasColumn('clients', 'salary_cost')) {
                    $table->decimal('salary_cost', 10, 2)->nullable()->after('billing_rate');
                }
                if (! Schema::hasColumn('clients', 'esi_rate')) {
                    $table->decimal('esi_rate', 5, 2)->nullable()->after('salary_cost');
                }
                if (! Schema::hasColumn('clients', 'pf_rate')) {
                    $table->decimal('pf_rate', 5, 2)->nullable()->after('esi_rate');
                }
                if (! Schema::hasColumn('clients', 'licensing_cost')) {
                    $table->decimal('licensing_cost', 10, 2)->nullable()->after('pf_rate');
                }
                if (! Schema::hasColumn('clients', 'administrative_overhead')) {
                    $table->decimal('administrative_overhead', 10, 2)->nullable()->after('licensing_cost');
                }
                if (! Schema::hasColumn('clients', 'gross_margin')) {
                    $table->decimal('gross_margin', 10, 2)->nullable()->after('administrative_overhead');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                if (Schema::hasColumn('clients', 'gross_margin')) {
                    $table->dropColumn('gross_margin');
                }
                if (Schema::hasColumn('clients', 'administrative_overhead')) {
                    $table->dropColumn('administrative_overhead');
                }
                if (Schema::hasColumn('clients', 'licensing_cost')) {
                    $table->dropColumn('licensing_cost');
                }
                if (Schema::hasColumn('clients', 'pf_rate')) {
                    $table->dropColumn('pf_rate');
                }
                if (Schema::hasColumn('clients', 'esi_rate')) {
                    $table->dropColumn('esi_rate');
                }
                if (Schema::hasColumn('clients', 'salary_cost')) {
                    $table->dropColumn('salary_cost');
                }
                if (Schema::hasColumn('clients', 'billing_rate')) {
                    $table->dropColumn('billing_rate');
                }
            });
        }
    }
};
