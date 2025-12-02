<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWebhookEventsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('webhook_events')) {
            Schema::create('webhook_events', function (Blueprint $table) {
                $table->id();
                $table->string('event_id')->unique();
                $table->json('payload');
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('webhook_events');
    }
}
