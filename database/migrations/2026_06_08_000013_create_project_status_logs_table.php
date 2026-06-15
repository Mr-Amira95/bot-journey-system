<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('log_key');
            $table->foreignId('changed_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('log_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_status_logs');
    }
};
