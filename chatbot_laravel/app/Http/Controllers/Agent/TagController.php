<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    // GET — list all tags for this tenant (used by the tag picker dropdown)
    public function index()
    {
        $tags = Tag::where('tenant_id', auth()->user()->tenant_id)
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    // POST — create a new tag for this tenant
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:40',
            'color' => 'nullable|string|max:20',
        ]);

        $tenantId = auth()->user()->tenant_id;

        // Reuse an existing tag with the same name instead of creating a duplicate
        $tag = Tag::firstOrCreate(
            ['tenant_id' => $tenantId, 'name' => trim($request->name)],
            ['color' => $request->color ?: '#6366f1']
        );

        return response()->json($tag);
    }

    public function destroy(Tag $tag)
    {
        if ($tag->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $tag->delete();

        return response()->json(['success' => true]);
    }

    // POST /conversations/{conversation}/tags — attach a tag (by id, or by name to create+attach)
    public function attach(Request $request, Conversation $conversation)
    {
        if ($conversation->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $tenantId = auth()->user()->tenant_id;

        if ($request->filled('tag_id')) {
            $tag = Tag::where('tenant_id', $tenantId)->findOrFail($request->tag_id);
        } else {
            $request->validate(['name' => 'required|string|max:40']);
            $tag = Tag::firstOrCreate(
                ['tenant_id' => $tenantId, 'name' => trim($request->name)],
                ['color' => $request->color ?: '#6366f1']
            );
        }

        $conversation->tags()->syncWithoutDetaching([$tag->id]);

        return response()->json([
            'success' => true,
            'tag' => $tag,
        ]);
    }

    // DELETE /conversations/{conversation}/tags/{tag} — detach a tag from a conversation
    public function detach(Conversation $conversation, Tag $tag)
    {
        if ($conversation->tenant_id !== auth()->user()->tenant_id || $tag->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }

        $conversation->tags()->detach($tag->id);

        return response()->json(['success' => true]);
    }
}
