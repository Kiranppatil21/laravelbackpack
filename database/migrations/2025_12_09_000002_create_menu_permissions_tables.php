<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_uuid')->nullable()->index();
            $table->string('menu_key')->index(); // Unique identifier for each menu item
            $table->string('menu_label'); // Display name
            $table->string('menu_url')->nullable(); // Route or URL
            $table->string('parent_key')->nullable(); // For nested menus
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['tenant_uuid', 'menu_key']);
        });

        Schema::create('role_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_uuid')->nullable()->index();
            $table->unsignedBigInteger('role_id');
            $table->unsignedBigInteger('menu_permission_id');
            $table->boolean('can_access')->default(true);
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('menu_permission_id')->references('id')->on('menu_permissions')->onDelete('cascade');
            $table->unique(['role_id', 'menu_permission_id'], 'role_menu_unique');
            $table->index(['tenant_uuid', 'role_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('role_menu_permissions');
        Schema::dropIfExists('menu_permissions');
    }
};
