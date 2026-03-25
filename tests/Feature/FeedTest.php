<?php

namespace Lontar\Blog\Tests\Feature;

use Lontar\Blog\Tests\TestCase;

class FeedTest extends TestCase
{
    // ── RSS ──────────────────────────────────────────────────────────────────

    public function test_rss_returns_200_with_correct_content_type(): void
    {
        $response = $this->get('/api/feed');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/rss+xml', $response->headers->get('Content-Type'));
    }

    public function test_rss_contains_required_channel_elements(): void
    {
        $response = $this->get('/api/feed');
        $body = $response->getContent();

        $this->assertStringContainsString('<rss version="2.0"', $body);
        $this->assertStringContainsString('<channel>', $body);
        $this->assertStringContainsString('<title>', $body);
        $this->assertStringContainsString('<link>', $body);
        $this->assertStringContainsString('<lastBuildDate>', $body);
    }

    public function test_rss_includes_published_posts(): void
    {
        $this->createPost(['title' => 'Hello World', 'slug' => 'hello-world', 'published_at' => now()->subHour()]);

        $body = $this->get('/api/feed')->getContent();

        $this->assertStringContainsString('<item>', $body);
        $this->assertStringContainsString('Hello World', $body);
        $this->assertStringContainsString('hello-world', $body);
    }

    public function test_rss_excludes_drafts_and_future_posts(): void
    {
        $this->createPost(['slug' => 'draft',  'published_at' => null]);
        $this->createPost(['slug' => 'future', 'published_at' => now()->addDay()]);

        $body = $this->get('/api/feed')->getContent();

        $this->assertStringNotContainsString('draft', $body);
        $this->assertStringNotContainsString('future', $body);
    }

    public function test_rss_is_valid_xml(): void
    {
        $this->createPost(['slug' => 'xml-test', 'published_at' => now()->subHour()]);

        $body = $this->get('/api/feed')->getContent();

        $doc = simplexml_load_string($body);
        $this->assertNotFalse($doc, 'RSS response is not valid XML');
    }

    public function test_rss_escapes_special_characters_in_title(): void
    {
        $this->createPost([
            'title'        => 'Cats & Dogs <test>',
            'slug'         => 'cats-dogs',
            'published_at' => now()->subHour(),
        ]);

        $body = $this->get('/api/feed')->getContent();

        $this->assertStringContainsString('Cats &amp; Dogs', $body);
        $this->assertStringNotContainsString('<test>', $body);
    }

    public function test_rss_uses_configured_post_url_template(): void
    {
        config(['lontar.feed.post_url' => 'https://example.com/blog/{slug}']);

        $this->createPost(['slug' => 'my-post', 'published_at' => now()->subHour()]);

        $body = $this->get('/api/feed')->getContent();

        $this->assertStringContainsString('https://example.com/blog/my-post', $body);
    }

    public function test_rss_caps_at_twenty_posts(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $this->createPost([
                'title'        => 'Post ' . $i,
                'slug'         => 'post-' . $i,
                'published_at' => now()->subMinutes($i),
            ]);
        }

        $body = $this->get('/api/feed')->getContent();

        $this->assertEquals(20, substr_count($body, '<item>'));
    }

    // ── Atom ─────────────────────────────────────────────────────────────────

    public function test_atom_returns_200_with_correct_content_type(): void
    {
        $response = $this->get('/api/feed/atom');

        $response->assertStatus(200);
        $this->assertStringContainsString('application/atom+xml', $response->headers->get('Content-Type'));
    }

    public function test_atom_contains_required_feed_elements(): void
    {
        $response = $this->get('/api/feed/atom');
        $body = $response->getContent();

        $this->assertStringContainsString('<feed xmlns="http://www.w3.org/2005/Atom">', $body);
        $this->assertStringContainsString('<title>', $body);
        $this->assertStringContainsString('<updated>', $body);
        $this->assertStringContainsString('<id>', $body);
    }

    public function test_atom_includes_published_posts(): void
    {
        $this->createPost(['title' => 'Atom Post', 'slug' => 'atom-post', 'published_at' => now()->subHour()]);

        $body = $this->get('/api/feed/atom')->getContent();

        $this->assertStringContainsString('<entry>', $body);
        $this->assertStringContainsString('Atom Post', $body);
        $this->assertStringContainsString('atom-post', $body);
    }

    public function test_atom_excludes_drafts_and_future_posts(): void
    {
        $this->createPost(['slug' => 'draft',  'published_at' => null]);
        $this->createPost(['slug' => 'future', 'published_at' => now()->addDay()]);

        $body = $this->get('/api/feed/atom')->getContent();

        $this->assertStringNotContainsString('draft', $body);
        $this->assertStringNotContainsString('future', $body);
    }

    public function test_atom_is_valid_xml(): void
    {
        $this->createPost(['slug' => 'atom-xml', 'published_at' => now()->subHour()]);

        $body = $this->get('/api/feed/atom')->getContent();

        $doc = simplexml_load_string($body);
        $this->assertNotFalse($doc, 'Atom response is not valid XML');
    }

    public function test_atom_entry_contains_content_and_summary(): void
    {
        $this->createPost([
            'slug'         => 'content-test',
            'excerpt'      => 'Short summary.',
            'body'         => '**Bold** content.',
            'published_at' => now()->subHour(),
        ]);

        $body = $this->get('/api/feed/atom')->getContent();

        $this->assertStringContainsString('<summary>', $body);
        $this->assertStringContainsString('<content type="html">', $body);
        $this->assertStringContainsString('Short summary.', $body);
    }
}
