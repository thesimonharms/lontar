# Lontar

A minimal, headless blog API package for Laravel. Ships a `Post` model, full CRUD controller, and API routes. Stores post bodies as Markdown and renders them server-side via `league/commonmark`.

No opinions on auth, frontend, or theming — bring your own.

---

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- `laravel/sanctum` installed in the consuming app (for authenticated endpoints)

---

## Installation

```bash
composer require lontar/blog
```

The service provider is auto-discovered. Run migrations:

```bash
php artisan migrate
```

That's it. The API routes are registered automatically under `/api`.

---

## Endpoints

### Public

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/posts` | Paginated list of published posts (`title`, `slug`, `excerpt`, `published_at`) |
| `GET` | `/api/posts/{slug}` | Full detail for a single published post |

Posts with a `null` `published_at` or a future `published_at` are treated as drafts and excluded from public responses.

### Authenticated (`auth:sanctum`)

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/posts/drafts` | Paginated list of unpublished posts |
| `POST` | `/api/posts` | Create a new post |
| `PUT` | `/api/posts/{slug}` | Update a post |
| `DELETE` | `/api/posts/{slug}` | Delete a post |
| `POST` | `/api/posts/{slug}/publish` | Set `published_at` to now |
| `POST` | `/api/posts/{slug}/unpublish` | Set `published_at` to null |

---

## Request Bodies

### `POST /api/posts`

```json
{
    "title": "My Post Title",
    "body": "Markdown content here.",
    "excerpt": "Optional short summary.",
    "published_at": "2026-03-10T00:00:00Z"
}
```

`slug` is derived automatically from `title`. `excerpt` and `published_at` are optional — omit `published_at` to save as a draft.

### `PUT /api/posts/{slug}`

Same fields as above, all optional. If `title` is updated, `slug` is regenerated.

---

## Markdown

Post bodies are stored as raw Markdown. The `Post` model exposes a `rendered_body` attribute that converts the body to HTML on access via `league/commonmark`:

```php
$post->rendered_body; // HTML string
```

Use this in your views when rendering post content. The raw `body` field is always available too.

---

## Post Model

```php
use Lontar\Blog\Models\Post;

// Published posts
Post::published()->get();

// All posts including drafts
Post::all();

// Find by slug
Post::where('slug', 'my-post')->firstOrFail();
```

The `published` scope filters to posts where `published_at` is not null and not in the future.

---

## Auth

This package ships with `auth:sanctum` on write routes but does not install or configure Sanctum. Set it up in your consuming app:

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Then ensure your `api` middleware group includes `sanctum` in `bootstrap/app.php` or `app/Http/Kernel.php`.

---

## License

MIT
