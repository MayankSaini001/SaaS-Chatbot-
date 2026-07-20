<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Conversation;
use App\Models\Message;

class RevenueController extends Controller
{
    public function index()
    {
        $planPrices = [
            'basic'      => 999,
            'pro'        => 2499,
            'enterprise' => 4999,
        ];

        $tenants = Tenant::where('is_active', true)->get();

        $mrr = 0;
        foreach ($tenants as $tenant) {
            $mrr += $planPrices[$tenant->plan] ?? 0;
        }

        $totalTenants    = Tenant::count();
        $activeTenants   = Tenant::where('is_active', true)->count();
        $totalConversations = Conversation::count();
        $totalMessages   = Message::count();

        $recentTenants = Tenant::latest()->take(10)->get()->map(function($t) use ($planPrices) {
            return [
                'name'    => $t->name,
                'email'   => $t->email,
                'plan'    => $t->plan,
                'amount'  => $planPrices[$t->plan] ?? 0,
                'status'  => $t->is_active ? 'Active' : 'Inactive',
                'date'    => $t->created_at->format('d M Y'),
            ];
        });

        return view('admin.revenue', compact(
            'mrr', 'totalTenants', 'activeTenants',
            'totalConversations', 'totalMessages', 'recentTenants'
        ));
    }
}