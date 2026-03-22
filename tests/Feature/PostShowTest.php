<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Tests\TestCase;

class PostShowTest extends TestCase
{
    public function test_returns_200_with_full_post_data_for_published_post(): void
    {
        $post = $this->createPost(['slug' => 'hello-world', 'published_at' => now()->subHour()]);

        $response = $this->getJson('/api/posts/hello-world');

        $response->assertStatus(200)
                 ->assertJsonFragment(['slug' => 'hello-world', 'title' => $post->title]);
    }

    public function test_returns_404_for_draft(): void
    {
        $this->createPost(['slug' => 'my-draft', 'published_at' => null]);

        $response = $this->getJson('/api/posts/my-draft');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_slug(): void
    {
        $response = $this->getJson('/api/posts/does-not-exist');

        $response->assertStatus(404);
    }

    public function test_rendered_body_is_included_in_response(): void
    {
        $this->createPost([
            'slug'         => 'with-markdown',
            'body'         => '# Hello',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/posts/with-markdown');

        $response->assertStatus(200);
        $this->assertArrayHasKey('rendered_body', $response->json());
        $this->assertStringContainsString('<h1>', $response->json('rendered_body'));
    }

    public function test_returns_404_for_future_dated_post(): void
    {
        $this->createPost([
            'slug'         => 'not-yet',
            'published_at' => now()->addDay(),
        ]);

        $response = $this->getJson('/api/posts/not-yet');

        $response->assertStatus(404);
    }

    public function test_body_is_included_in_response(): void
    {
        $this->createPost([
            'slug'         => 'with-body',
            'body'         => 'Full body content here.',
            'published_at' => now()->subHour(),
        ]);

        $response = $this->getJson('/api/posts/with-body');

        $response->assertStatus(200);
        $this->assertArrayHasKey('body', $response->json());
        $this->assertEquals('Full body content here.', $response->json('body'));
    }
}
