<?php

namespace Lontar\Blog\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use League\CommonMark\CommonMarkConverter;

class Post extends Model
{
    protected $fillable = ['title', 'slug', 'body', 'excerpt', 'published_at'];

    protected $appends = ['rendered_body'];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected function renderedBody(): Attribute
    {
        return Attribute::get(
            fn () => $this->body !== null ? (new CommonMarkConverter())->convert($this->body)->getContent() : null
        );
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }
}
