<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                // Annual, Sick, Unpaid, Maternity …
            $table->boolean('is_paid')->default(true);
            $table->unsignedSmallInteger('max_days_per_year')->nullable(); // null = unlimited
            $table->boolean('requires_approval')->default(true);
            $table->string('color')->nullable();                   // hex for calendar UI
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
