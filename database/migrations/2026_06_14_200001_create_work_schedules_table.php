<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('start_time');                          // e.g. "09:00"
            $table->string('end_time');                            // e.g. "17:00"
            $table->json('working_days');                          // ["Mon","Tue","Wed","Thu","Fri"]
            $table->unsignedInteger('break_duration_minutes')->default(60);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
    }
};
