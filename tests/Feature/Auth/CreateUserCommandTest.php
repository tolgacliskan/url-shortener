<?php

declare(strict_types=1);

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a verified user with a personal workspace', function () {
    $this->artisan('create-user', ['email' => 'kitchiko@example.com', '--name' => 'Kitchiko'])
        ->expectsQuestion('Password', 'a-long-password')
        ->assertSuccessful();

    $user = User::where('email', 'kitchiko@example.com')->firstOrFail();

    expect($user->email_verified_at)->not->toBeNull()
        ->and($user->currentWorkspace->plan->internal_id)->toBe('unlimited');
});

it('puts the workspace on the plan it is given', function () {
    $this->artisan('create-user', ['email' => 'kitchiko@example.com', '--name' => 'Kitchiko', '--plan' => 'unlimited'])
        ->expectsQuestion('Password', 'a-long-password')
        ->assertSuccessful();

    $user = User::where('email', 'kitchiko@example.com')->firstOrFail();

    expect($user->currentWorkspace->plan->internal_id)->toBe('unlimited');
});

it('refuses a plan that does not exist', function () {
    $this->artisan('create-user', ['email' => 'kitchiko@example.com', '--plan' => 'nope'])
        ->assertFailed();

    expect(User::where('email', 'kitchiko@example.com')->exists())->toBeFalse();
});

it('refuses an email that is already taken', function () {
    User::factory()->withWorkspace()->create(['email' => 'kitchiko@example.com']);

    $this->artisan('create-user', ['email' => 'kitchiko@example.com', '--name' => 'Kitchiko'])
        ->expectsQuestion('Password', 'a-long-password')
        ->assertFailed();

    expect(User::where('email', 'kitchiko@example.com')->count())->toBe(1);
});

it('seeds the unlimited plan as private', function () {
    $plan = Plan::where('internal_id', 'unlimited')->firstOrFail();

    expect($plan->is_private)->toBeTrue()
        ->and($plan->max_links)->toBeNull();
});
