<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('widgets', function (Blueprint $table) {
        $table->id();
        $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
        $table->string('embed_token')->unique();
        $table->string('color')->default('#6366f1');
        $table->string('position')->default('bottom-right');
        $table->string('greeting')->default('Hi! How can we help you?');
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
}
};
