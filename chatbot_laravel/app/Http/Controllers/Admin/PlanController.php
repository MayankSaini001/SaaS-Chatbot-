<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;

class PlanController extends Controller
{
    public function index()
    {
        $plans = [
            [
                'name'       => 'Basic',
                'price'      => 999,
                'agents'     => 2,
                'chats'      => 500,
                'tenants'    => Tenant::where('plan', 'basic')->count(),
            ],
            [
                'name'       => 'Pro',
                'price'      => 2499,
                'agents'     => 10,
                'chats'      => 2000,
                'tenants'    => Tenant::where('plan', 'pro')->count(),
            ],
            [
                'name'       => 'Enterprise',
                'price'      => 4999,
                'agents'     => 999,
                'chats'      => 99999,
                'tenants'    => Tenant::where('plan', 'enterprise')->count(),
            ],
        ];

        return view('admin.plans', compact('plans'));
    }
}