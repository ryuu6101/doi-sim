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
        Schema::create('esim_reports', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date_time')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('service_code')->nullable();
            $table->string('action')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('old_esim')->nullable();
            $table->string('new_esim')->nullable();
            $table->string('account')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esim_reports');
    }
};
