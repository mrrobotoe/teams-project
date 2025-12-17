<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamInviteDestroyRequest;
use App\Http\Requests\TeamInviteStoreRequest;
use App\Models\Team;
use App\Models\TeamInvite;
use Illuminate\Http\Request;

class TeamInviteController extends Controller
{
    public function store(TeamInviteStoreRequest $request, Team $team)
    {
        $invite = $team->invites()->create([
            'email' => $request->email,
            'token' => str()->random(30)
        ]);

        return back()->withStatus('team-invited');
    }

    public function destroy(TeamInviteDestroyRequest $request, Team $team, TeamInvite $teamInvite)
    {
        $teamInvite->delete();

        return redirect()->route('team.edit');
    }
}
