<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->string('direct_manager')->nullable()->after('department');
        });
    }

    public function down(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->dropColumn('direct_manager');
        });
    }
};
