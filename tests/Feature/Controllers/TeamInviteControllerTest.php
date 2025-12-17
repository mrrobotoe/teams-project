<?php

use App\Http\Middleware\TeamsPermission;
use App\Models\Team;
use App\Models\TeamInvite;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

afterEach(function () {
    Str::createRandomStringsNormally();
});

it('create an invite', function() {
    $user = User::factory()->create();

    // Mock the token generator
    Str::createRandomStringsUsing(fn () => 'abc');

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam), [
            'email' => $email = 'mcurie@example.com'
        ])
        ->assertRedirect();

    assertDatabaseHas('team_invites', [
        'team_id' => $user->currentTeam->id,
        'email' => $email,
        'token' => 'abc'
    ]);
});

it('requires an email address', function() {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam), [])
        ->assertSessionHasErrors('email');
});

it('fails to create an invite if email already used', function() {
    $user = User::factory()->create();

    TeamInvite::factory()->create([
        'team_id' => $user->currentTeam->id,
        'email' => $email = 'mcurie@example.com'
    ]);

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam), [
            'email' => $email
        ])
        ->assertInvalid();
});

it('creates invite if email already invited to another team', function() {
    $user = User::factory()->create();

    TeamInvite::factory()
        ->for(Team::factory())
        ->create([
            'email' => $email = 'mcurie@example.com'
        ]);

    actingAs($user)
        ->post(route('team.invites.store', $user->currentTeam), [
            'email' => $email
        ])
        ->assertValid();
});

it('fails to send invite without permission', function() {
    $user = User::factory()->create();

    $user->teams()->attach(
        $anotherTeam = Team::factory()->create()
    );

    setPermissionsTeamId($anotherTeam->id);

    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->post(route('team.invites.store', $anotherTeam), [
            'email' => 'test@example.com'
        ])
        ->assertForbidden();
});

it('can revoke an invite', function() {
    $user = User::factory()->create();

    $invite = TeamInvite::factory()->create([
        'team_id' => $user->currentTeam->id
    ]);

    actingAs($user)
        ->delete(route('team.invites.destroy', [$user->currentTeam, $invite]))
        ->assertRedirect();

    assertDatabaseMissing('team_invites', [
        'team_id' => $user->currentTeam->id,
        'token' => $invite->token,
        'team' => $invite->email,
    ]);
});

it('cannot revoke an invite without permission', function() {
    $user = User::factory()->create();

    $user->teams()->attach(
        $anotherTeam = Team::factory()->create()
    );

    $invite = TeamInvite::factory()->create([
        'team_id' => $anotherTeam->id
    ]);

    setPermissionsTeamId($anotherTeam->id);

    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->delete(route('team.invites.destroy', [$user->currentTeam, $invite]))
        ->assertForbidden();
});
