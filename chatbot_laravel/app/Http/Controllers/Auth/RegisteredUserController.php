<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OwnerWelcomeMail;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'company_name' => ['required', 'string', 'max:255'],
            'email'        => [
                'required', 'string', 'email', 'max:255',
                'unique:users,email',
                'unique:tenants,email',
            ],
            'password'     => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $plainPassword = $request->password;

        // Tenant banao
        $tenant = \App\Models\Tenant::create([
            'name'          => $request->company_name,
            'email'         => $request->email,
            'slug'          => \Illuminate\Support\Str::slug($request->company_name . '-' . time()),
            'is_active'     => true,
            'plan'          => 'trial',
            'trial_ends_at' => now()->addDays(7),
        ]);

        // Widget banao
        \App\Models\Widget::create([
            'tenant_id'   => $tenant->id,
            'embed_token' => \Illuminate\Support\Str::random(32),
            'color'       => '#6366f1',
            'position'    => 'bottom-right',
            'greeting'    => 'Hi! How can we help you?',
            'is_active'   => true,
        ]);

        // User banao
        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($plainPassword),
            'role'      => 'owner',
            'tenant_id' => $tenant->id,
        ]);

        event(new Registered($user));
        Auth::login($user);

        // Welcome email — error aaye toh bhi dashboard pe jaao
        try {
            Mail::to($user->email)->send(
                new OwnerWelcomeMail($user->name, $user->email, $plainPassword, $request->company_name)
            );
        } catch (\Exception $e) {
            Log::error('Owner welcome email failed: ' . $e->getMessage());
        }

        return redirect()->route('agent.dashboard');
    }
}