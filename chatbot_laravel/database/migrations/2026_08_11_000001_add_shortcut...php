<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canned_responses', function (Blueprint $table) {
            // Agent Productivity — slash-command shortcut, e.g. "/pricing"
            $table->string('shortcut', 50)->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('canned_responses', function (Blueprint $table) {
            $table->dropColumn('shortcut');
        });
    }
};