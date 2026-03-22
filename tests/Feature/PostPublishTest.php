<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostPublishTest extends TestCase
{
    public function test_publish_returns_401_without_auth(): void
    {
        $this->createPost(['slug' => 'auth-test-pub', 'published_at' => null]);

        $response = $this->postJson('/api/posts/auth-test-pub/publish');

        $response->assertStatus(401);
    }

    public function test_unpublish_returns_401_without_auth(): void
    {
        $this->createPost(['slug' => 'auth-test-unpub', 'published_at' => now()->subHour()]);

        $response = $this->postJson('/api/posts/auth-test-unpub/unpublish');

        $response->assertStatus(401);
    }

    public function test_publish_sets_published_at_to_now(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'unpublished', 'published_at' => null]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts/unpublished/publish');

        $response->assertStatus(200);
        $this->assertNotNull($response->json('published_at'));
        $this->assertDatabaseMissing('posts', ['slug' => 'unpublished', 'published_at' => null]);
    }

    public function test_unpublish_sets_published_at_to_null(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'to-unpublish', 'published_at' => now()->subHour()]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts/to-unpublish/unpublish');

        $response->assertStatus(200);
        $this->assertNull($response->json('published_at'));
        $this->assertDatabaseHas('posts', ['slug' => 'to-unpublish', 'published_at' => null]);
    }

    public function test_publish_returns_404_for_nonexistent_slug(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts/ghost/publish');

        $response->assertStatus(404);
    }

    public function test_unpublish_returns_404_for_nonexistent_slug(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts/ghost/unpublish');

        $response->assertStatus(404);
    }
}
