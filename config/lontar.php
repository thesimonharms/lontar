<?php

return [

    'feed' => [

        /*
         * The title shown in the RSS/Atom feed <title> element.
         * Defaults to APP_NAME.
         */
        'title' => env('APP_NAME', 'Blog'),

        /*
         * A short description of the feed (used by RSS only).
         */
        'description' => '',

        /*
         * Template for individual post URLs inside the feed.
         * {slug} is replaced with the post's slug.
         *
         * Example: 'https://example.com/blog/{slug}'
         *
         * When null, falls back to url('/posts/{slug}').
         */
        'post_url' => null,

    ],

];
