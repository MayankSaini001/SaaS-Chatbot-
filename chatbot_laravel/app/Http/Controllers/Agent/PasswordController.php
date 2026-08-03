<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    public function edit()
    {
        return view('agent.password.change');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:6|confirmed',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $file = $request->file('avatar');

        $filename = 'avatar_' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();

        $destination = dirname(public_path()) . '/../chatbot/uploads/avatars';

        if (!file_exists($destination)) {
            mkdir($destination, 0775, true);
        }

        $file->move($destination, $filename);

        $url = url('/uploads/avatars/' . $filename);

        auth()->user()->update(['avatar' => $url]);

        return back()->with('success', 'Profile photo updated!');
    }

    public function removeAvatar()
    {
        auth()->user()->update(['avatar' => null]);

        return back()->with('success', 'Profile photo removed.');
    }
}