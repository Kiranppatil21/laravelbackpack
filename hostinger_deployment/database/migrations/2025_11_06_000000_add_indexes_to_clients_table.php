<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Some DB drivers (notably sqlite) will fail if an index already exists.
        // Query the DB (PRAGMA for sqlite) to avoid attempting to create duplicate indexes.
        $existing = [];
        try {
            $existing = array_map(fn($r) => $r->name, DB::select("PRAGMA index_list('clients')"));
        } catch (\Throwable $e) {
            // If PRAGMA isn't available (non-sqlite), leave $existing empty and rely on Schema builder which will error if duplicate.
            $existing = [];
        }

        Schema::table('clients', function (Blueprint $table) use ($existing) {
            // add indexes to speed up searches by name/email used in APIs and typeahead
            if (! in_array('clients_email_index', $existing, true)) {
                $table->index('email', 'clients_email_index');
            }
            if (! in_array('clients_name_index', $existing, true)) {
                $table->index('name', 'clients_name_index');
            }
        });
    }

    public function down()
    {
        // Only attempt to drop indexes if they exist to avoid errors on some DBs
        try {
            $existing = array_map(fn($r) => $r->name, DB::select("PRAGMA index_list('clients')"));
        } catch (\Throwable $e) {
            $existing = [];
        }

        Schema::table('clients', function (Blueprint $table) use ($existing) {
            if (in_array('clients_email_index', $existing, true)) {
                $table->dropIndex('clients_email_index');
            }
            if (in_array('clients_name_index', $existing, true)) {
                $table->dropIndex('clients_name_index');
            }
        });
    }
};
