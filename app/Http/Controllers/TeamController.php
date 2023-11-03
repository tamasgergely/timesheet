<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Team::class);

        return inertia('Teams/Index', [
            'teams' => $this->getTeams(),
            'teamLeaders' => Auth::user()->role_id === Role::ADMIN ? $this->getTeamLeaders() : null,
            'keyword' => $request->search,
            'team' => null
        ]);
    }

    public function create()
    {
        $this->authorize('create', Team::class);

        return inertia('Teams/Create', [
            'leaders' => Auth::user()->role_id === Role::ADMIN ? $this->getTeamLeaders() : null
        ]);
    }

    public function store(StoreTeamRequest $request)
    {
        $this->authorize('create', Team::class);

        Team::create([
            'name' => $request->name,
            'leader_id' => Auth::user()->role_id === Role::TEAM_LEADER 
                            ? Auth::id()
                            : $request->leader_id
        ]);
        
        return redirect()->route('teams.index')
                         ->with('success', 'Team saved successfully!');
    }

    public function update(UpdateTeamRequest $request, Team $team)
    {
        $this->authorize('update', $team);

        $team->name = $request->name;
        $team->leader_id = Auth::user()->role_id === Role::ADMIN ? $request->leader_id : Auth::id();

        $team->save();

        return redirect()->route('teams.index', ['page' => $request->page, 'search'=> $request->keyword])
                         ->with('success', 'Team updated successfully!');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        $team->delete();

        return redirect()->route('teams.index')
                         ->with('success', 'Team deleted successfully!');
    }

    private function getTeams()
    {
        return Team::filter(request()->only('search'))
                    ->with('user:id,name', 'users:id,name')
                    ->paginate(10)
                    ->withQueryString();
    }

    private function getTeamLeaders()
    {
        return User::where('role_id', Role::TEAM_LEADER)
                    ->get()
                    ->transform(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'created_at' => $user->created_at,
                        'updated_at' => $user->updated_at
                    ]);
    }
}