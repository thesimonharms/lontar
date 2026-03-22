<?php

namespace Lontar\Blog\Tests\Unit;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostModelTest extends TestCase
{
    public function test_scope_published_returns_published_posts(): void
    {
        $post = Post::create([
            'title'        => 'Published',
            'slug'         => 'published',
            'body'         => 'Body.',
            'published_at' => now()->subHour(),
        ]);

        $results = Post::published()->pluck('slug')->all();

        $this->assertContains('published', $results);
    }

    public function test_scope_published_excludes_drafts(): void
    {
        Post::create([
            'title' => 'Draft',
            'slug'  => 'draft',
            'body'  => 'Body.',
        ]);

        $results = Post::published()->pluck('slug')->all();

        $this->assertNotContains('draft', $results);
    }

    public function test_scope_published_excludes_future_dated_posts(): void
    {
        Post::create([
            'title'        => 'Future',
            'slug'         => 'future',
            'body'         => 'Body.',
            'published_at' => now()->addDay(),
        ]);

        $results = Post::published()->pluck('slug')->all();

        $this->assertNotContains('future', $results);
    }

    public function test_rendered_body_converts_markdown_to_html(): void
    {
        $post = new Post(['body' => '# Hello']);

        $rendered = $post->rendered_body;

        $this->assertStringContainsString('<h1>Hello</h1>', $rendered);
    }

    public function test_rendered_body_returns_null_when_body_is_null(): void
    {
        $post = new Post(['body' => null]);

        $this->assertNull($post->rendered_body);
    }
}
