<?php

namespace Tests\Unit;

use Tests\TestCase;

class GoogleCalendarScopesTest extends TestCase
{
    private const EVENTS_SCOPE = 'https://www.googleapis.com/auth/calendar.events';
    private const FULL_SCOPE = 'https://www.googleapis.com/auth/calendar';

    public function test_production_with_legacy_enabled_returns_only_calendar_events_scope(): void
    {
        $this->setEnvironment('production');
        config(['services.google.calendar.use_legacy_full_scope' => true]);

        $this->assertSame([self::EVENTS_SCOPE], google_calendar_scopes());
    }

    public function test_production_with_legacy_disabled_returns_only_calendar_events_scope(): void
    {
        $this->setEnvironment('production');
        config(['services.google.calendar.use_legacy_full_scope' => false]);

        $this->assertSame([self::EVENTS_SCOPE], google_calendar_scopes());
    }

    public function test_non_production_with_legacy_disabled_returns_only_calendar_events_scope(): void
    {
        $this->setEnvironment('testing');
        config(['services.google.calendar.use_legacy_full_scope' => false]);

        $this->assertSame([self::EVENTS_SCOPE], google_calendar_scopes());
    }

    public function test_non_production_with_legacy_enabled_returns_calendar_events_and_full_calendar_scopes(): void
    {
        $this->setEnvironment('local');
        config(['services.google.calendar.use_legacy_full_scope' => true]);

        $this->assertSame([self::EVENTS_SCOPE, self::FULL_SCOPE], google_calendar_scopes());
    }

    private function setEnvironment(string $environment): void
    {
        config(['app.env' => $environment]);
        $this->app->detectEnvironment(fn () => $environment);
    }
}
