<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use Illuminate\Http\Request;

class CannedResponseController extends Controller
{
    public function index()
    {
        $responses = CannedResponse::where('tenant_id', auth()->user()->tenant_id)
            ->with('author')
            ->latest()
            ->get();

        return view('agent.canned-responses.index', compact('responses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:100',
            'body'  => 'required|string|max:2000',
        ]);

        CannedResponse::create([
            'tenant_id' => auth()->user()->tenant_id,
            'user_id'   => auth()->id(),
            'title'     => $request->title,
            'body'      => $request->body,
        ]);

        return redirect()->route('agent.canned-responses')
            ->with('success', 'Canned response added!');
    }

    public function destroy(CannedResponse $cannedResponse)
    {
        if ($cannedResponse->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $cannedResponse->delete();

        return redirect()->route('agent.canned-responses')
            ->with('success', 'Canned response deleted.');
    }

    // Used by the conversation page to fetch the list for the quick-insert popover
    public function data()
    {
        $responses = CannedResponse::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('title')
            ->get(['id', 'title', 'body']);

        return response()->json($responses);
    }
}
