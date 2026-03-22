# API Reference

Base Path: `/api`

All API routes enforce an IP allowlist. Requests from non-allowlisted IPs receive `404`. Authenticated routes additionally require a Bearer token issued via the `AdminSeeder`.

---

## Authentication

Authenticated endpoints require a Sanctum token in the `Authorization` header:

```
Authorization: Bearer <token>
```

Tokens are issued once via:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## Posts

### `GET /api/posts`

Returns a paginated list of published posts.

**Auth:** None

**Response `200`:**
```json
{
    "data": [
        {
            "title": "My Post",
            "slug": "my-post",
            "excerpt": "A short summary.",
            "published_at": "2026-03-10T00:00:00.000000Z"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1
}
```

Posts with a `null` or future `published_at` are excluded.

---

### `GET /api/posts/{slug}`

Returns full detail for a single published post.

**Auth:** None

**Response `200`:**
```json
{
    "id": 1,
    "title": "My Post",
    "slug": "my-post",
    "body": "Raw Markdown content.",
    "rendered_body": "<p>Rendered HTML content.</p>",
    "excerpt": "A short summary.",
    "published_at": "2026-03-10T00:00:00.000000Z",
    "created_at": "2026-03-10T00:00:00.000000Z",
    "updated_at": "2026-03-10T00:00:00.000000Z"
}
```

Returns `404` if the post does not exist or is not published.

---

### `GET /api/posts/drafts`

Returns a paginated list of unpublished posts.

**Auth:** Required

**Response `200`:**
```json
{
    "data": [
        {
            "title": "Unpublished Post",
            "slug": "unpublished-post",
            "excerpt": null,
            "created_at": "2026-03-20T00:00:00.000000Z"
        }
    ],
    "current_page": 1,
    "per_page": 15,
    "total": 1
}
```

---

### `POST /api/posts`

Creates a new post. Slug is derived automatically from `title`.

**Auth:** Required

**Request body:**
```json
{
    "title": "My Post Title",
    "body": "Markdown content here.",
    "excerpt": "Optional short summary.",
    "published_at": "2026-03-10T00:00:00Z"
}
```

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `title` | string | Yes | Max 255 characters. Slug is auto-generated. |
| `body` | string | Yes | Markdown. |
| `excerpt` | string | No | Plain text summary. |
| `published_at` | datetime | No | Omit to save as draft. |

**Response `201`:** Full post object.

---

### `PUT /api/posts/{slug}`

Updates an existing post. All fields are optional. If `title` is changed, `slug` is regenerated.

**Auth:** Required

**Request body:** Same fields as `POST /api/posts`, all optional.

**Response `200`:** Updated post object.

---

### `DELETE /api/posts/{slug}`

Deletes a post permanently.

**Auth:** Required

**Response `204`:** No content.

---

### `POST /api/posts/{slug}/publish`

Sets `published_at` to the current timestamp.

**Auth:** Required

**Response `200`:** Updated post object.

---

### `POST /api/posts/{slug}/unpublish`

Sets `published_at` to `null`, reverting the post to draft status.

**Auth:** Required

**Response `200`:** Updated post object.

---

## Projects

### `DELETE /api/projects/{id}`

Deletes a project permanently.

**Auth:** Required

**Response `204`:** No content.

---

### `POST /api/projects/{id}/publish`

Sets the project's `published_at` to the current timestamp, making it visible on the portfolio.

**Auth:** Required

**Response `200`:**
```json
{
    "id": 1,
    "name": "My Project",
    "description": "Project description.",
    "github_url": "https://github.com/...",
    "project_url": "https://...",
    "tech_stack": "Laravel, React",
    "published_at": "2026-03-21T00:00:00.000000Z",
    "created_at": "2026-03-10T00:00:00.000000Z",
    "updated_at": "2026-03-21T00:00:00.000000Z"
}
```

---

### `POST /api/projects/{id}/unpublish`

Sets the project's `published_at` to `null`, hiding it from the portfolio.

**Auth:** Required

**Response `200`:** Updated project object.

---

## Error Responses

| Status | Meaning |
|--------|---------|
| `404` | Request came from a non-allowlisted IP, or the resource does not exist |
| `401` | Missing or invalid Bearer token |
| `422` | Validation failed — response body includes field-level error messages |
| `204` | Successful delete — no response body |
