<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Tenant;
use App\Models\Widget;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user (aap khud)
        User::create([
            'name' => 'Admin',
            'email' => 'admin@chatbot.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'tenant_id' => null,
        ]);

        // Test tenant (buyer)
        $tenant = Tenant::create([
            'name' => 'Test Company',
            'email' => 'tenant@test.com',
            'slug' => 'test-company',
            'is_active' => true,
            'plan' => 'basic',
        ]);

        // Test agent (support team)
        User::create([
            'name' => 'Test Agent',
            'email' => 'agent@test.com',
            'password' => Hash::make('password'),
            'role' => 'agent',
            'tenant_id' => $tenant->id,
        ]);

        // Widget for test tenant
        Widget::create([
            'tenant_id' => $tenant->id,
            'embed_token' => Str::random(32),
            'color' => '#6366f1',
            'position' => 'bottom-right',
            'greeting' => 'Hi! How can we help you?',
            'is_active' => true,
        ]);
    }
}