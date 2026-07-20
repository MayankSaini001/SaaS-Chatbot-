<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Conversation;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTenants = Tenant::count();
        $totalAgents = User::where('role', 'agent')->count();
        $totalConversations = Conversation::count();
        $activeConversations = Conversation::where('status', 'open')->count();
        $recentTenants = Tenant::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalTenants',
            'totalAgents',
            'totalConversations',
            'activeConversations',
            'recentTenants'
        ));
    }
}
