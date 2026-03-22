<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostStoreTest extends TestCase
{
    public function test_returns_401_without_auth(): void
    {
        $response = $this->postJson('/api/posts', [
            'title' => 'Some Title',
            'body'  => 'Some body.',
        ]);

        $response->assertStatus(401);
    }

    public function test_creates_post_and_returns_201(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'My New Post',
                'body'  => 'Body content.',
            ]);

        $response->assertStatus(201)
                 ->assertJsonFragment(['title' => 'My New Post']);
        $this->assertDatabaseHas('posts', ['title' => 'My New Post']);
    }

    public function test_auto_generates_slug_from_title(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Hello World Post',
                'body'  => 'Body.',
            ]);

        $response->assertStatus(201);
        $this->assertEquals('hello-world-post', $response->json('slug'));
    }

    public function test_handles_slug_collision(): void
    {
        $user = $this->createUser();
        Post::create(['title' => 'Collision', 'slug' => 'collision', 'body' => 'Body.']);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Collision',
                'body'  => 'Another body.',
            ]);

        $response->assertStatus(201);
        $this->assertEquals('collision-1', $response->json('slug'));

        $response2 = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Collision',
                'body'  => 'Yet another body.',
            ]);

        $response2->assertStatus(201);
        $this->assertEquals('collision-2', $response2->json('slug'));
    }

    public function test_validates_required_title(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'body' => 'Body without title.',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['title']);
    }

    public function test_validates_required_body(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Title without body',
            ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['body']);
    }

    public function test_creates_as_draft_when_no_published_at(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title' => 'Draft Post',
                'body'  => 'Draft body.',
            ]);

        $response->assertStatus(201);
        $this->assertNull($response->json('published_at'));
        $this->assertDatabaseHas('posts', ['slug' => 'draft-post', 'published_at' => null]);
    }

    public function test_creates_as_published_when_published_at_provided(): void
    {
        $user = $this->createUser();
        $date = now()->subHour()->toDateTimeString();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/posts', [
                'title'        => 'Published Post',
                'body'         => 'Published body.',
                'published_at' => $date,
            ]);

        $response->assertStatus(201);
        $this->assertNotNull($response->json('published_at'));
    }
}
