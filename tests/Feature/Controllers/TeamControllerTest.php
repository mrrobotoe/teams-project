<?php

use App\Models\Team;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('switches the current team for the user', function () {
    $user = User::factory()->create();

    $user->teams()->attach(
        $team = Team::factory()->create()
    );

    actingAs($user)
        ->patch(route('team.set-current', $team))
        ->assertRedirect();

    expect($user->currentTeam->id)->toBe($team->id);
});

it('cannot switch to a team that the user does not belong to', function () {
    $user = User::factory()->create();

    $anotherTeam = Team::factory()->create();

    actingAs($user)
        ->patch(route('team.set-current', $anotherTeam))
        ->assertForbidden();

    expect($user->currentTeam->id)->not->toBe($anotherTeam->id);
});

it('can update team', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->patch(route('team.update', $user->currentTeam), [
            'name' => 'New Team Name'
        ])
        ->assertRedirect();

    expect($user->fresh()->currentTeam->name)->toBe('New Team Name');
});

it ('cannot update if not in team', function() {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    actingAs($anotherUser)
        ->patch(route('team.update', $user->currentTeam), [
            'name' => 'New Team Name'
        ])
        ->assertForbidden();

    expect($user->currentTeam->name)->not->toBe('New Team Name');
});
