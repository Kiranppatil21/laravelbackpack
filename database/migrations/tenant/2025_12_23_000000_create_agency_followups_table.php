<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('agency_followups')) {
            Schema::create('agency_followups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agency_id')->constrained('agencies')->cascadeOnDelete();
                $table->foreignId('lead_person_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('communication_type')->nullable();
                $table->text('notes')->nullable();
                $table->json('attachments')->nullable();
                $table->timestamp('followed_up_at')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_followups');
    }
};
