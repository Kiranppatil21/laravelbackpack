<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // First, remove any existing duplicate emails by updating them
            $duplicates = DB::table('clients')
                ->select('email', DB::raw('COUNT(*) as count'))
                ->groupBy('email')
                ->having('count', '>', 1)
                ->get();
            
            foreach ($duplicates as $duplicate) {
                $clients = DB::table('clients')->where('email', $duplicate->email)->get();
                foreach ($clients as $index => $client) {
                    if ($index > 0) { // Keep the first one, modify the rest
                        DB::table('clients')
                            ->where('id', $client->id)
                            ->update(['email' => $client->email . '_duplicate_' . $client->id]);
                    }
                }
            }
            
            // Now add the unique constraint
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });
    }
};
