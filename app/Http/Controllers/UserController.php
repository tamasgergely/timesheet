<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $allTeams = [];
        foreach (Team::pluck('id', 'name') as $team => $id) {
            $allTeams[] = ['label' => $team, 'value' => $id];
        };

        return inertia('Users/Index', [
            'users' => $this->getUsers(),
            'keyword' => $request->search,
            'roles' => Auth::user()->role_id === Role::ADMIN ? Role::all() : null,
            'teams' => Auth::user()->role_id === Role::ADMIN ? $allTeams : null
        ]);
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $allTeams = [];
        foreach (Team::pluck('id', 'name') as $team => $id) {
            $allTeams[] = ['label' => $team, 'value' => $id];
        };

        return inertia('Users/Create', [
            'roles' => Role::all(),
            'teams' => $allTeams
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $avatar = null;
        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('images', 'public');
            
            if (!$path) {
                return back()->withErrors(['avatar' => 'An error occurred while uploading your avatar'])->withInput();
            }

            $uploadedFile = $request->file('avatar');
            $avatar = $uploadedFile->hashName();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
            'password' => Hash::make($request->password),
            'avatar' => $avatar
        ]);

        $user->teams()->attach(Arr::pluck($request->teams ?? [], 'value'));

        return redirect()->route('users.index')
                         ->with('success', 'User saved successfully!');
    }

    public function edit(Request $request, User $user)
    {     
        $this->authorize('update', $user);

        $userTeams = [];
        foreach ($user->teams->pluck('id', 'name') as $team => $id) {
            $userTeams[] = ['label' => $team, 'value' => $id];
        };

        $user = collect([$user])->transform(function ($user) use ($userTeams) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'team_id' => $user->team_id,
                'avatar' => $user->avatar,
                'teams' => $userTeams,
                'created_at' => $user->created_at,
                'role_id' => $user->role_id
            ];
        })->first();

        $allTeams = [];
        foreach (Team::pluck('id', 'name') as $team => $id) {
            $allTeams[] = ['label' => $team, 'value' => $id];
        };

        return inertia($user['id'] === Auth::id() ? 'Profile/Edit' : 'Users/Index', [
            'keyword' => $request->search,
            'user' => $user,
            'users' => $this->getUsers(),
            'roles' => Auth::user()->role_id === Role::ADMIN ? Role::all() : null,
            'teams' => Auth::user()->role_id === Role::ADMIN ? $allTeams : null
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role_id = Auth::user()->role_id === Role::ADMIN ? $request->role_id : Auth::user()->role_id;

        $user->save();
    
        if (auth()->user()->role_id === Role::ADMIN) {
            $user->teams()->sync(Arr::pluck($request->teams ?? [], 'value'));
        }

        if ($user['id'] === Auth::id()) {
            return redirect()->back()
                             ->with('success', 'User updated successfully!');
        }

        return redirect()->route('users.index', ['page' => $request->page, 'search'=> $request->keyword])
                         ->with('success', 'User updated successfully!');
    }


    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', 'User deleted successfully!');
    }
    
    private function getUsers()
    {
        $users = User::filter(request()->only('search'))
                     ->with('role', 'teams')
                     ->paginate(10)
                     ->withQueryString();


        foreach ($users as $user) {
            $userTeams = [];
            foreach ($user->teams->pluck('id', 'name') as $team => $id) {
                $userTeams[] = ['label' => $team, 'value' => $id];
            };
            $user->teams = $userTeams;
        }

        return $users->through(fn ($user) => [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'role' => $user->role->name,
                            'role_id' => $user->role_id,
                            'teams' => $user->teams,
                            'avatar' => $user->avatar,
                            'created_at' => $user->created_at,
                            'updated_at' => $user->updated_at
                        ]);           
    }
}
