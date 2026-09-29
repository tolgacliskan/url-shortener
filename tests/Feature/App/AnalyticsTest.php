<?php

declare(strict_types=1);

use App\Models\Link;
use App\Models\LinkStat;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->withWorkspace()->create();
});

it('returns a successful response', function () {
    $response = $this
        ->actingAs($this->user)
        ->get(route('analytics.index'));

    $response->assertStatus(200);
});

/**
 * The timezone arrives from the browser and is handed to the database as the
 * argument to `at time zone` / `CONVERT_TZ`. Two things follow: a zone the
 * database does not know is an error on PostgreSQL and a silently null bucket
 * on MySQL, so it has to be validated; and browsers still report deprecated
 * IANA aliases, so the validation cannot be the plain `timezone` rule.
 */
it('accepts the deprecated timezone aliases browsers still send', function (string $timezone) {
    $this->actingAs($this->user)
        ->getJson(route('analytics.statistics', [
            'start' => '2026-06-01',
            'end' => '2026-06-15',
            'group' => 'day',
            'timezone' => $timezone,
        ]))
        ->assertSuccessful();
})->with([
    'Asia/Calcutta (Indian clients report this, not Asia/Kolkata)' => 'Asia/Calcutta',
    'Brazil/East' => 'Brazil/East',
    'Asia/Kolkata' => 'Asia/Kolkata',
    'UTC' => 'UTC',
]);

it('rejects a timezone the database would choke on', function () {
    $this->actingAs($this->user)
        ->getJson(route('analytics.statistics', [
            'start' => '2026-06-01',
            'end' => '2026-06-15',
            'group' => 'day',
            'timezone' => 'Not/A_Timezone',
        ]))
        ->assertJsonValidationErrors('timezone');
});

/**
 * At 22:07 UTC on the 29th it is already the 30th in Istanbul. Drawing the
 * default range from the UTC date ended it a day early, and the evening's
 * clicks fell outside it.
 */
it('ends the default range on today in the display timezone', function () {
    config(['lua.timezone' => 'Europe/Istanbul']);
    $this->travelTo(CarbonImmutable::parse('2026-09-29 22:07:00', 'UTC'));

    $this->actingAs($this->user)
        ->get(route('analytics.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('end', '2026-09-30')
            ->where('start', '2026-09-01')
        );
});

it('lists the events of today in the display timezone', function () {
    config(['lua.timezone' => 'Europe/Istanbul']);
    $this->travelTo(CarbonImmutable::parse('2026-09-29 22:07:00', 'UTC'));

    $workspace = $this->user->currentWorkspace;
    $link = Link::factory()->create(['workspace_id' => $workspace->id]);

    LinkStat::factory()->create([
        'workspace_id' => $workspace->id,
        'link_id' => $link->id,
        'created_at' => CarbonImmutable::parse('2026-09-29 21:52:00', 'UTC'),
    ]);

    $this->actingAs($this->user)
        ->get(route('events.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('end', '2026-09-30')
            ->has('table.data', 1)
        );
});

it('shares the display timezone with the frontend', function () {
    config(['lua.timezone' => 'Europe/Istanbul']);

    $this->actingAs($this->user)
        ->get(route('analytics.index'))
        ->assertInertia(fn (Assert $page) => $page->where('timezone', 'Europe/Istanbul'));
});
