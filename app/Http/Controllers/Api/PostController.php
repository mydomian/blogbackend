<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // Public: List all posts
    public function index()
    {
        return Post::latest()->get();
    }

    // Public: Show single post
    public function show($slug)
    {
        return Post::where('slug', $slug)->firstOrFail();
    }

    // Admin: Create Post
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'image' => 'nullable|image|max:2048'
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
        }

        $post = Post::create([
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'content' => $request->content,
            'image' => $path
        ]);

        return response()->json($post, 201);
    }

    // Admin: Update Post
    public function update(Request $request, Post $post)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
        ]);

        $post->title = $request->title;
        $post->slug = Str::slug($request->title);
        $post->content = $request->content;

        if ($request->hasFile('image')) {
            if ($post->image) Storage::disk('public')->delete($post->image);
            $post->image = $request->file('image')->store('posts', 'public');
        }

        $post->save();
        return response()->json($post);
    }

    // Admin: Delete Post
    public function destroy(Post $post)
    {
        if ($post->image) Storage::disk('public')->delete($post->image);
        $post->delete();
        return response()->json("Deleted", 204);
    }
}
