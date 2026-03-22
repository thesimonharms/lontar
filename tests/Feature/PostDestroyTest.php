<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostDestroyTest extends TestCase
{
    public function test_returns_401_without_auth(): void
    {
        $this->createPost(['slug' => 'delete-me']);

        $response = $this->deleteJson('/api/posts/delete-me');

        $response->assertStatus(401);
    }

    public function test_deletes_post_and_returns_204(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'to-delete', 'published_at' => now()->subHour()]);

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/posts/to-delete');

        $response->assertStatus(204);
    }

    public function test_returns_404_for_nonexistent_slug(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/posts/ghost-post');

        $response->assertStatus(404);
    }

    public function test_post_is_actually_removed_from_database(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'gone-post', 'title' => 'Gone Post']);

        $this->actingAs($user, 'sanctum')
            ->deleteJson('/api/posts/gone-post')
            ->assertStatus(204);

        $this->assertDatabaseMissing('posts', ['slug' => 'gone-post']);
    }
}
