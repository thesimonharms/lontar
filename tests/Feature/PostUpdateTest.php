<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostUpdateTest extends TestCase
{
    public function test_returns_401_without_auth(): void
    {
        $this->createPost(['slug' => 'some-post']);

        $response = $this->putJson('/api/posts/some-post', ['title' => 'New Title']);

        $response->assertStatus(401);
    }

    public function test_updates_post_successfully(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'update-me', 'title' => 'Old Title', 'published_at' => now()->subHour()]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/update-me', ['title' => 'New Title']);

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'New Title']);
    }

    public function test_returns_404_for_nonexistent_slug(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/does-not-exist', ['title' => 'Whatever']);

        $response->assertStatus(404);
    }

    public function test_regenerates_slug_when_title_changes(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'old-slug', 'title' => 'Old Slug']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/old-slug', ['title' => 'Brand New Title']);

        $response->assertStatus(200);
        $this->assertEquals('brand-new-title', $response->json('slug'));
        $this->assertDatabaseMissing('posts', ['slug' => 'old-slug']);
        $this->assertDatabaseHas('posts', ['slug' => 'brand-new-title']);
    }

    public function test_handles_slug_collision_on_update(): void
    {
        $user = $this->createUser();
        Post::create(['title' => 'Existing', 'slug' => 'existing', 'body' => 'Body.']);
        $this->createPost(['slug' => 'to-update', 'title' => 'To Update']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/to-update', ['title' => 'Existing']);

        $response->assertStatus(200);
        $this->assertEquals('existing-1', $response->json('slug'));
    }

    public function test_updating_title_to_same_value_does_not_change_slug(): void
    {
        $user = $this->createUser();
        $this->createPost(['slug' => 'same-title', 'title' => 'Same Title']);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/same-title', ['title' => 'Same Title']);

        $response->assertStatus(200);
        $this->assertEquals('same-title', $response->json('slug'));
    }

    public function test_partial_update_leaves_other_fields_intact(): void
    {
        $user = $this->createUser();
        $this->createPost([
            'slug'    => 'partial-update',
            'title'   => 'Original Title',
            'body'    => 'Original body.',
            'excerpt' => 'Original excerpt.',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->putJson('/api/posts/partial-update', ['excerpt' => 'New excerpt.']);

        $response->assertStatus(200);
        $this->assertEquals('Original Title', $response->json('title'));
        $this->assertEquals('Original body.', $response->json('body'));
        $this->assertEquals('New excerpt.', $response->json('excerpt'));
    }
}
