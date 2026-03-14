<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();
        $this->assertSafeTestingDatabase($app);

        return $app;
    }

    private function assertSafeTestingDatabase(Application $app): void
    {
        if ($app->environment() !== 'testing') {
            return;
        }

        $allowUnsafe = filter_var((string) env('TESTS_ALLOW_UNSAFE_DB', 'false'), FILTER_VALIDATE_BOOLEAN);
        if ($allowUnsafe) {
            return;
        }

        $connection = (string) $app['config']->get('database.default');
        $database = (string) $app['config']->get("database.connections.{$connection}.database", '');
        $databaseNormalized = strtolower(trim($database));

        $isSqliteMemory = $connection === 'sqlite' && $databaseNormalized === ':memory:';
        $isClearlyTestingDatabase = $databaseNormalized !== '' && str_contains($databaseNormalized, 'test');

        if ($isSqliteMemory || $isClearlyTestingDatabase) {
            return;
        }

        throw new \RuntimeException(sprintf(
            'Unsafe testing database detected: connection="%s" database="%s". Configure a dedicated test DB (e.g. "*_test") or sqlite :memory:.',
            $connection,
            $database
        ));
    }
}
