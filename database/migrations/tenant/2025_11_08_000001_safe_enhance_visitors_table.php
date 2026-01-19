<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Check and add columns only if they don't exist
            if (!Schema::hasColumn('visitors', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('id_value');
            }
            if (!Schema::hasColumn('visitors', 'qr_code')) {
                $table->string('qr_code')->unique()->nullable()->after('photo_path');
            }
            if (!Schema::hasColumn('visitors', 'status')) {
                $table->enum('status', ['active', 'blocked', 'pending_approval'])->default('active')->after('qr_code');
            }
            if (!Schema::hasColumn('visitors', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
            
            // Identity verification
            if (!Schema::hasColumn('visitors', 'id_verified')) {
                $table->boolean('id_verified')->default(false)->after('notes');
            }
            if (!Schema::hasColumn('visitors', 'id_verified_at')) {
                $table->timestamp('id_verified_at')->nullable()->after('id_verified');
            }
            if (!Schema::hasColumn('visitors', 'verified_by')) {
                $table->unsignedBigInteger('verified_by')->nullable()->after('id_verified_at');
            }
            
            // Contact and emergency information
            if (!Schema::hasColumn('visitors', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->after('verified_by');
            }
            if (!Schema::hasColumn('visitors', 'emergency_contact_phone')) {
                $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');
            }
            if (!Schema::hasColumn('visitors', 'address')) {
                $table->string('address')->nullable()->after('emergency_contact_phone');
            }
            
            // Visit purpose and approval
            if (!Schema::hasColumn('visitors', 'purpose')) {
                $table->string('purpose')->nullable()->after('address');
            }
            if (!Schema::hasColumn('visitors', 'pre_approved')) {
                $table->boolean('pre_approved')->default(false)->after('purpose');
            }
            if (!Schema::hasColumn('visitors', 'approved_by')) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('pre_approved');
            }
            if (!Schema::hasColumn('visitors', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('approved_by');
            }
            
            // Security and compliance
            if (!Schema::hasColumn('visitors', 'background_check_required')) {
                $table->boolean('background_check_required')->default(false)->after('approved_at');
            }
            if (!Schema::hasColumn('visitors', 'background_check_status')) {
                $table->enum('background_check_status', ['pending', 'passed', 'failed', 'not_required'])->default('not_required')->after('background_check_required');
            }
            if (!Schema::hasColumn('visitors', 'background_check_date')) {
                $table->timestamp('background_check_date')->nullable()->after('background_check_status');
            }
            
            // Blacklist and watchlist
            if (!Schema::hasColumn('visitors', 'on_watchlist')) {
                $table->boolean('on_watchlist')->default(false)->after('background_check_date');
            }
            if (!Schema::hasColumn('visitors', 'watchlist_reason')) {
                $table->text('watchlist_reason')->nullable()->after('on_watchlist');
            }
            
            // Health screening
            if (!Schema::hasColumn('visitors', 'temperature')) {
                $table->decimal('temperature', 5, 2)->nullable()->after('watchlist_reason');
            }
            if (!Schema::hasColumn('visitors', 'health_screening_passed')) {
                $table->boolean('health_screening_passed')->default(true)->after('temperature');
            }
            if (!Schema::hasColumn('visitors', 'health_questions')) {
                $table->json('health_questions')->nullable()->after('health_screening_passed');
            }
            
            // Metadata
            if (!Schema::hasColumn('visitors', 'metadata')) {
                $table->json('metadata')->nullable()->after('health_questions');
            }
            if (!Schema::hasColumn('visitors', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // Add foreign key constraints if they don't exist
        try {
            Schema::table('visitors', function (Blueprint $table) {
                if (!$this->foreignKeyExists('visitors', 'visitors_verified_by_foreign')) {
                    $table->foreign('verified_by')->references('id')->on('users')->nullOnDelete();
                }
                if (!$this->foreignKeyExists('visitors', 'visitors_approved_by_foreign')) {
                    $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
                }
            });
        } catch (Exception $e) {
            // Foreign keys might already exist or users table might not exist
        }

        // Add indexes if they don't exist
        try {
            $this->addIndexIfNotExists('visitors', ['status', 'created_at'], 'visitors_status_created_at_index');
            $this->addIndexIfNotExists('visitors', ['pre_approved', 'approved_at'], 'visitors_pre_approved_approved_at_index');
            $this->addIndexIfNotExists('visitors', ['on_watchlist'], 'visitors_on_watchlist_index');
            $this->addIndexIfNotExists('visitors', ['background_check_status'], 'visitors_background_check_status_index');
        } catch (Exception $e) {
            // Indexes might already exist
        }
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            // Drop foreign keys first
            try {
                if ($this->foreignKeyExists('visitors', 'visitors_verified_by_foreign')) {
                    $table->dropForeign(['verified_by']);
                }
                if ($this->foreignKeyExists('visitors', 'visitors_approved_by_foreign')) {
                    $table->dropForeign(['approved_by']);
                }
            } catch (Exception $e) {
                // Foreign keys might not exist
            }
            
            // Drop indexes
            try {
                $this->dropIndexIfExists('visitors', 'visitors_status_created_at_index');
                $this->dropIndexIfExists('visitors', 'visitors_pre_approved_approved_at_index');
                $this->dropIndexIfExists('visitors', 'visitors_on_watchlist_index');
                $this->dropIndexIfExists('visitors', 'visitors_background_check_status_index');
            } catch (Exception $e) {
                // Indexes might not exist
            }
            
            // Drop columns if they exist
            $columnsToRemove = [
                'photo_path', 'qr_code', 'status', 'notes',
                'id_verified', 'id_verified_at', 'verified_by',
                'emergency_contact_name', 'emergency_contact_phone', 'address',
                'purpose', 'pre_approved', 'approved_by', 'approved_at',
                'background_check_required', 'background_check_status', 'background_check_date',
                'on_watchlist', 'watchlist_reason',
                'temperature', 'health_screening_passed', 'health_questions',
                'metadata', 'deleted_at'
            ];
            
            foreach ($columnsToRemove as $column) {
                if (Schema::hasColumn('visitors', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function foreignKeyExists($table, $foreignKey): bool
    {
        $foreignKeys = Schema::getConnection()
                           ->getDoctrineSchemaManager()
                           ->listTableForeignKeys($table);
        
        foreach ($foreignKeys as $key) {
            if ($key->getName() === $foreignKey) {
                return true;
            }
        }
        
        return false;
    }

    private function addIndexIfNotExists($table, $columns, $indexName): void
    {
        $indexes = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableIndexes($table);
        
        if (!isset($indexes[$indexName])) {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
                $blueprint->index($columns, $indexName);
            });
        }
    }

    private function dropIndexIfExists($table, $indexName): void
    {
        $indexes = Schema::getConnection()
                        ->getDoctrineSchemaManager()
                        ->listTableIndexes($table);
        
        if (isset($indexes[$indexName])) {
            Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                $blueprint->dropIndex($indexName);
            });
        }
    }
};