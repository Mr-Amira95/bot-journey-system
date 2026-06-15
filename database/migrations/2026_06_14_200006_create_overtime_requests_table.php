<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('start_time');                          // e.g. "17:00"
            $table->string('end_time');                            // e.g. "20:00"
            $table->decimal('hours', 5, 2);
            $table->decimal('multiplier', 4, 2)->default(1.5);    // pay rate multiplier
            $table->text('reason')->nullable();
            $table->string('status')->default('pending');          // OvertimeStatus enum
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('payroll_item_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
