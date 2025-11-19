<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('statutory_reports', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // gst, tds, pf, esic
            $table->date('period_start');
            $table->date('period_end');
            $table->json('payload')->nullable();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('statutory_reports');
    }
};
