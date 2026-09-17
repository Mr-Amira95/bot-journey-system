<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->string('department')->nullable()->after('project_id');
        });
    }

    public function down(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->dropColumn('department');
            $table->foreignId('department_id')->nullable()->after('project_id')
                ->constrained('departments')->nullOnDelete();
        });
    }
};
