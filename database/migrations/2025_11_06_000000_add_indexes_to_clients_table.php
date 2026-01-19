<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Check for existing indexes based on DB driver
        $existing = [];
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $existing = array_map(fn($r) => $r->name, DB::select("PRAGMA index_list('clients')"));
            } elseif ($driver === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM clients");
                $existing = array_map(fn($r) => $r->Key_name, $indexes);
            }
        } catch (\Throwable $e) {
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
        // Check for existing indexes based on DB driver
        $existing = [];
        try {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $existing = array_map(fn($r) => $r->name, DB::select("PRAGMA index_list('clients')"));
            } elseif ($driver === 'mysql') {
                $indexes = DB::select("SHOW INDEX FROM clients");
                $existing = array_map(fn($r) => $r->Key_name, $indexes);
            }
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
