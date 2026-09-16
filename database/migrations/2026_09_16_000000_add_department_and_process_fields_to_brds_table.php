<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('project_id')
                ->constrained('departments')->nullOnDelete();

            $table->text('as_is_workflow')->nullable()->after('scope');
            $table->text('as_is_pain_points')->nullable()->after('as_is_workflow');
            $table->text('as_is_existing_systems')->nullable()->after('as_is_pain_points');
            $table->text('to_be_workflow')->nullable()->after('as_is_existing_systems');
            $table->text('to_be_benefits')->nullable()->after('to_be_workflow');
            $table->text('kpis')->nullable()->after('to_be_benefits');

            $table->dropColumn('stakeholders');
        });
    }

    public function down(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->text('stakeholders')->nullable();

            $table->dropColumn([
                'as_is_workflow',
                'as_is_pain_points',
                'as_is_existing_systems',
                'to_be_workflow',
                'to_be_benefits',
                'kpis',
            ]);

            $table->dropConstrainedForeignId('department_id');
        });
    }
};
