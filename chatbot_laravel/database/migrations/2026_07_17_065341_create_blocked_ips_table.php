<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_ips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('ip_address');
            $table->string('reason')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Ek tenant ke andar ek IP sirf ek hi baar blocked list mein ho.
            $table->unique(['tenant_id', 'ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
    }
};
