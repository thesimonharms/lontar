<?php

namespace Lontar\Blog\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Schema;
use Lontar\Blog\BlogServiceProvider;
use Lontar\Blog\Models\Post;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function getPackageProviders($app): array
    {
        return [
            \Laravel\Sanctum\SanctumServiceProvider::class,
            BlogServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('auth.guards.sanctum', [
            'driver'   => 'sanctum',
            'provider' => 'users',
        ]);
        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model'  => User::class,
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $sanctumPath = __DIR__ . '/../vendor/laravel/sanctum/database/migrations';
        if (is_dir($sanctumPath)) {
            $this->loadMigrationsFrom($sanctumPath);
        }
    }

    protected function createPost(array $attributes = []): Post
    {
        return Post::create(array_merge([
            'title'        => 'Test Post',
            'slug'         => 'test-post',
            'body'         => 'This is the body.',
            'excerpt'      => 'This is the excerpt.',
            'published_at' => now(),
        ], $attributes));
    }

    protected function createUser(): User
    {
        return User::forceCreate([
            'name'     => 'Test User',
            'email'    => 'test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
        ]);
    }
}
