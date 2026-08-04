<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\AutoReply;
use Illuminate\Http\Request;

class AutoReplyController extends Controller
{
    public function index()
    {
        $autoReplies = AutoReply::where('tenant_id', auth()->user()->tenant_id)
            ->latest()
            ->get();

        return view('agent.auto-replies.index', compact('autoReplies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'keyword' => 'required|string|max:100',
            'reply'   => 'required|string|max:2000',
        ]);

        AutoReply::create([
            'tenant_id' => auth()->user()->tenant_id,
            'keyword'   => trim($request->keyword),
            'reply'     => $request->reply,
            'is_active' => true,
        ]);

        return redirect()->route('agent.auto-replies')
            ->with('success', 'Auto-reply added!');
    }

    public function toggle(AutoReply $autoReply)
    {
        if ($autoReply->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $autoReply->update(['is_active' => !$autoReply->is_active]);

        return back()->with('success', $autoReply->is_active ? 'Auto-reply enabled.' : 'Auto-reply paused.');
    }

    public function destroy(AutoReply $autoReply)
    {
        if ($autoReply->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $autoReply->delete();

        return redirect()->route('agent.auto-replies')
            ->with('success', 'Auto-reply deleted.');
    }
}
