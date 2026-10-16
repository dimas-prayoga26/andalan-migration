<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Runs before RefreshDatabase migrates, so a cached or wrong config can never wipe a real database.
     *
     * @return array<class-string, class-string>
     */
    protected function setUpTraits()
    {
        $this->ensureTestingDatabase();

        return parent::setUpTraits();
    }

    private function ensureTestingDatabase(): void
    {
        $connection = (string) config('database.default');
        $database = (string) config("database.connections.{$connection}.database");

        if (! str_ends_with($database, '_testing')) {
            throw new RuntimeException(
                "Tests must run against a *_testing database, but [{$database}] is configured. Run `php artisan config:clear` and check phpunit.xml."
            );
        }
    }
}
