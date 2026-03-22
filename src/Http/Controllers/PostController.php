<?php

namespace Lontar\Blog\Http\Controllers;

use Lontar\Blog\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->orderByDesc('published_at')
            ->paginate(15, ['title', 'slug', 'excerpt', 'published_at']);

        return response()->json($posts);
    }

    public function show(string $slug)
    {
        $post = Post::published()->where('slug', $slug)->firstOrFail();

        return response()->json($post);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'body'         => 'required|string',
            'excerpt'      => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        $base = Str::slug($data['title']);
        $slug = $base;
        $i = 1;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }
        $data['slug'] = $slug;

        $post = Post::create($data);

        return response()->json($post, 201);
    }

    public function update(Request $request, string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'title'        => 'sometimes|string|max:255',
            'body'         => 'sometimes|string',
            'excerpt'      => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        if (isset($data['title'])) {
            $base = Str::slug($data['title']);
            $slug = $base;
            $i = 1;
            while (Post::where('slug', $slug)->where('id', '!=', $post->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $data['slug'] = $slug;
        }

        $post->update($data);
        $post->refresh();

        return response()->json($post);
    }

    public function destroy(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->delete();

        return response()->json(null, 204);
    }

    public function drafts()
    {
        $posts = Post::whereNull('published_at')
            ->orderByDesc('created_at')
            ->paginate(15, ['title', 'slug', 'excerpt', 'created_at']);

        return response()->json($posts);
    }

    public function publish(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->update(['published_at' => now()]);

        return response()->json($post);
    }

    public function unpublish(string $slug)
    {
        $post = Post::where('slug', $slug)->firstOrFail();
        $post->update(['published_at' => null]);

        return response()->json($post);
    }
}
