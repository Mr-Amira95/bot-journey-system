<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->json('ai_canvas_data')->nullable()->after('share_token');
            $table->timestamp('ai_canvas_generated_at')->nullable()->after('ai_canvas_data');
        });
    }

    public function down(): void
    {
        Schema::table('brds', function (Blueprint $table) {
            $table->dropColumn(['ai_canvas_data', 'ai_canvas_generated_at']);
        });
    }
};
