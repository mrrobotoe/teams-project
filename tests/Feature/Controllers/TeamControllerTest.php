<?php

use App\Http\Middleware\TeamsPermission;
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

it('can not update a team without permission', function() {
    $user = User::factory()->create();

    $user->teams()->attach(
        $team = Team::factory()->create()
    );

    setPermissionsTeamId($team->id);

    actingAs($user)
        ->withoutMiddleware(TeamsPermission::class)
        ->patch(route('team.update', $team), [
            'name' => 'New Team Name'
        ])
        ->assertForbidden();
});

// cannot leave a team if we have one team remaining
it('can leave a team', function () {
    $user = User::factory()
        ->has(Team::factory())
        ->create();

    $teamToLeave = $user->currentTeam;

    actingAs($user)
        ->post(route('team.leave', $teamToLeave))
        ->assertRedirect('/dashboard');

    expect($user->fresh()->teams->contains($teamToLeave->id))->toBeFalse()
        ->and($user->fresh()->currentTeam->id)->not->toBe($teamToLeave->id);
});

// cannot leave a team if we do not belong to it
it('cannot leave a team if we have one team remaining', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('team.leave', $user->currentTeam))
        ->assertForbidden();


});

it('cannot leave a team that we don\'t belong to', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    actingAs($user)
        ->post(route('team.leave', $anotherUser->currentTeam))
        ->assertForbidden();

    expect($user->currentTeam->id)->not->toBe($anotherUser->currentTeam->id);
});

it('should show a list of memebers', function () {
    $user = User::factory()->create();

    $user->currentTeam->members()->attach(
        $members = User::factory()->times(2)->create()
    );

    actingAs($user)
        ->get('/team')
        ->assertSeeText($members->pluck('email')->toArray())
        ->assertSeeText($members->pluck('name')->toArray())
    ;
});
