<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \App\Models\User::where('role', 'agent')
            ->get()
            ->groupBy('tenant_id')
            ->each(function ($users) {
                $users->first()->update(['role' => 'owner']);
            });
    }

    public function down(): void
    {
        \App\Models\User::where('role', 'owner')
            ->update(['role' => 'agent']);
    }
};