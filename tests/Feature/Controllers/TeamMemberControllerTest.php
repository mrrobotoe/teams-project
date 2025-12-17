<?php

use App\Http\Middleware\TeamsPermission;
use App\Models\User;
use function Pest\Laravel\actingAs;

it('can delete a team member', function () {
    $user = User::factory()->create();

    $user->currentTeam->members()->attach(
        $member = User::factory()->create()
    );

    $member->currentTeam()->associate($user->currentTeam)->save();

    actingAs($user)
        ->delete(route('team.members.destroy', [$user->currentTeam, $member]))
        ->assertRedirect();

    expect($user->fresh()->currentTeam->members->contains($member->id))->toBeFalse()
        ->and($member->fresh()->currentTeam)->not->toEqual($user->currentTeam);
});

it('can not remove a member from a team without permission', function () {
    $user = User::factory()->create();

    $anotherUser = User::factory()->create();

    $user->currentTeam->members()->attach(
        $member = User::factory()->create()
    );

    $member->currentTeam()->associate($user->currentTeam)->save();

    setPermissionsTeamId($user->currentTeam->id);

    actingAs($anotherUser)
        ->withoutMiddleware(TeamsPermission::class)
        ->delete(route('team.members.destroy', [$user->currentTeam, $member]))
        ->assertForbidden();

    expect($user->fresh()->currentTeam->members->contains($member->id))->toBeTrue();
});

it('can not remove self from team', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->delete(route('team.members.destroy', [$user->currentTeam, $user]))
        ->assertForbidden();
});
