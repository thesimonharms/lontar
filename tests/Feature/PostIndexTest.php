<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Models\Post;
use Lontar\Blog\Tests\TestCase;

class PostIndexTest extends TestCase
{
    public function test_returns_200_with_paginated_structure(): void
    {
        $this->createPost(['title' => 'First', 'slug' => 'first', 'published_at' => now()->subHour()]);
        $this->createPost(['title' => 'Second', 'slug' => 'second', 'published_at' => now()->subMinutes(30)]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'data',
                     'current_page',
                     'per_page',
                     'total',
                 ]);
    }

    public function test_only_returns_published_posts(): void
    {
        $published = $this->createPost(['title' => 'Published', 'slug' => 'published', 'published_at' => now()->subHour()]);
        $draft = $this->createPost(['title' => 'Draft', 'slug' => 'draft', 'published_at' => null]);
        $future = $this->createPost(['title' => 'Future', 'slug' => 'future', 'published_at' => now()->addDay()]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $data = $response->json('data');
        $slugs = array_column($data, 'slug');

        $this->assertContains('published', $slugs);
        $this->assertNotContains('draft', $slugs);
        $this->assertNotContains('future', $slugs);
    }

    public function test_response_does_not_include_body(): void
    {
        $this->createPost(['slug' => 'no-body-test', 'published_at' => now()->subHour()]);

        $response = $this->getJson('/api/posts');

        $response->assertStatus(200);
        $item = $response->json('data.0');

        $this->assertArrayHasKey('title', $item);
        $this->assertArrayHasKey('slug', $item);
        $this->assertArrayHasKey('excerpt', $item);
        $this->assertArrayHasKey('published_at', $item);
        $this->assertArrayNotHasKey('body', $item);
    }

    public function test_pagination_works_for_large_result_sets(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Post::create([
                'title'        => 'Post ' . $i,
                'slug'         => 'post-' . $i,
                'body'         => 'Body',
                'published_at' => now()->subMinutes($i),
            ]);
        }

        $page1 = $this->getJson('/api/posts?page=1');
        $page1->assertStatus(200);
        $this->assertCount(15, $page1->json('data'));
        $this->assertEquals(20, $page1->json('total'));

        $page2 = $this->getJson('/api/posts?page=2');
        $page2->assertStatus(200);
        $this->assertCount(5, $page2->json('data'));
    }
}
