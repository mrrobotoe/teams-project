<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamMemberDestroyRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function destroy(TeamMemberDestroyRequest $request, Team $team, User $user)
    {
        $team->members()->detach($user);

        $user->currentTeam()->associate($user->teams()->first())->save();

        return redirect()->route('team.edit');
    }
}
