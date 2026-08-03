<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;

class TenantController extends Controller
{
    public function index()
    {
        $tenants = Tenant::withCount(['conversations', 'widgets'])
                         ->latest()
                         ->get();

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(Tenant $tenant)
    {
        $agents = User::where('tenant_id', $tenant->id)->get();
        $conversations = $tenant->conversations()->latest()->take(10)->get();
        $totalMessages = 0;
        foreach ($tenant->conversations as $conv) {
            $totalMessages += $conv->messages()->count();
        }

        return view('admin.tenants.show', compact('tenant', 'agents', 'conversations', 'totalMessages'));
    }

    public function toggle(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);
        return back()->with('success', 'Tenant status updated!');
    }
}