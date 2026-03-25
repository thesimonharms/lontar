<?php

namespace Lontar\Blog\Http\Controllers;

use Lontar\Blog\Models\Post;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class FeedController extends Controller
{
    private const LIMIT = 20;

    public function rss()
    {
        $posts    = $this->getPosts();
        $feedUrl  = url('/api/feed');
        $siteUrl  = config('app.url', '');
        $title    = config('lontar.feed.title', config('app.name', 'Blog'));
        $desc     = config('lontar.feed.description', '');
        $lastBuild = $posts->isNotEmpty()
            ? $posts->first()->published_at->toRfc2822String()
            : now()->toRfc2822String();

        $items = $posts->map(function (Post $post) {
            $link    = $this->postUrl($post->slug);
            $pubDate = $post->published_at->toRfc2822String();
            $content = $post->excerpt ?? strip_tags($post->rendered_body ?? '');

            return
                "<item>\n" .
                '  <title>' . $this->esc($post->title) . "</title>\n" .
                '  <link>' . $this->esc($link) . "</link>\n" .
                '  <guid isPermaLink="true">' . $this->esc($link) . "</guid>\n" .
                '  <pubDate>' . $pubDate . "</pubDate>\n" .
                '  <description><![CDATA[' . $content . "]]></description>\n" .
                '</item>';
        })->implode("\n");

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n" .
            "<channel>\n" .
            '  <title>' . $this->esc($title) . "</title>\n" .
            '  <link>' . $this->esc($siteUrl) . "</link>\n" .
            '  <description>' . $this->esc($desc) . "</description>\n" .
            "  <language>en</language>\n" .
            '  <lastBuildDate>' . $lastBuild . "</lastBuildDate>\n" .
            '  <atom:link href="' . $this->esc($feedUrl) . '" rel="self" type="application/rss+xml"/>' . "\n" .
            $items . "\n" .
            "</channel>\n" .
            '</rss>';

        return response($xml, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    public function atom()
    {
        $posts   = $this->getPosts();
        $feedUrl = url('/api/feed/atom');
        $siteUrl = config('app.url', '');
        $title   = config('lontar.feed.title', config('app.name', 'Blog'));
        $updated = $posts->isNotEmpty()
            ? $posts->first()->published_at->toAtomString()
            : now()->toAtomString();

        $entries = $posts->map(function (Post $post) {
            $link      = $this->postUrl($post->slug);
            $published = $post->published_at->toAtomString();
            $updated   = $post->updated_at->toAtomString();
            $summary   = $post->excerpt ?? '';
            $content   = $post->rendered_body ?? '';

            return
                "<entry>\n" .
                '  <title>' . $this->esc($post->title) . "</title>\n" .
                '  <link href="' . $this->esc($link) . '"/>' . "\n" .
                '  <id>' . $this->esc($link) . "</id>\n" .
                '  <published>' . $published . "</published>\n" .
                '  <updated>' . $updated . "</updated>\n" .
                '  <summary><![CDATA[' . $summary . "]]></summary>\n" .
                '  <content type="html"><![CDATA[' . $content . "]]></content>\n" .
                '</entry>';
        })->implode("\n");

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
            '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n" .
            '  <title>' . $this->esc($title) . "</title>\n" .
            '  <link href="' . $this->esc($siteUrl) . '"/>' . "\n" .
            '  <link rel="self" href="' . $this->esc($feedUrl) . '"/>' . "\n" .
            '  <id>' . $this->esc($feedUrl) . "</id>\n" .
            '  <updated>' . $updated . "</updated>\n" .
            $entries . "\n" .
            '</feed>';

        return response($xml, 200, ['Content-Type' => 'application/atom+xml; charset=UTF-8']);
    }

    private function getPosts(): Collection
    {
        return Post::published()
            ->orderByDesc('published_at')
            ->limit(self::LIMIT)
            ->get(['title', 'slug', 'excerpt', 'body', 'published_at', 'updated_at']);
    }

    private function postUrl(string $slug): string
    {
        $template = config('lontar.feed.post_url');

        if ($template) {
            return str_replace('{slug}', $slug, $template);
        }

        return url('/posts/' . $slug);
    }

    private function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
