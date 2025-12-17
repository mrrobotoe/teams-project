<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    public function destroy(Team $team, User $user)
    {
        $team->members()->detach($user);

        $user->currentTeam()->associate($user->teams()->first())->save();

        return redirect()->route('team.edit');
    }
}
