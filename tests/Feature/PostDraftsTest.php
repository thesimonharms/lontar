<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Tests\TestCase;

class PostDraftsTest extends TestCase
{
    public function test_returns_401_without_auth(): void
    {
        $response = $this->getJson('/api/posts/drafts');

        $response->assertStatus(401);
    }

    public function test_returns_only_drafts_when_authenticated(): void
    {
        $user = $this->createUser();
        $draft = $this->createPost(['slug' => 'a-draft', 'published_at' => null]);
        $published = $this->createPost(['slug' => 'a-published', 'published_at' => now()->subHour()]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/posts/drafts');

        $response->assertStatus(200);
        $slugs = array_column($response->json('data'), 'slug');

        $this->assertContains('a-draft', $slugs);
        $this->assertNotContains('a-published', $slugs);
    }

    public function test_drafts_route_takes_priority_over_slug(): void
    {
        // A published post whose slug is literally "drafts"
        $this->createPost(['slug' => 'drafts', 'published_at' => now()->subHour()]);

        // Unauthenticated request should get 401, not the post
        $response = $this->getJson('/api/posts/drafts');

        $response->assertStatus(401);
    }

    public function test_excludes_published_posts(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'draft-1', 'published_at' => null]);
        $this->createPost(['slug' => 'draft-2', 'published_at' => null]);
        $this->createPost(['slug' => 'pub-1', 'published_at' => now()->subHour()]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/posts/drafts');

        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('total'));
    }
}
