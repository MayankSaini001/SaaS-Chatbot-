<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->boolean('business_hours_enabled')->default(false)->after('is_active');
            $table->string('business_hours_timezone')->default('Asia/Kolkata')->after('business_hours_enabled');
            $table->json('business_hours')->nullable()->after('business_hours_timezone');
        });
    }

    public function down(): void
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropColumn(['business_hours_enabled', 'business_hours_timezone', 'business_hours']);
        });
    }
};
