<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Team;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;

use function Symfony\Component\String\b;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Clients/Index', [
            'clients' => $this->getClients(),
            'keyword' => $request->search,
            'teams' => Auth::user()->role_id === Role::ADMIN ? Team::all() 
                        : Auth::user()->ledTeams->transform(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name
            ])
        ]);
    }

    public function create()
    {
        return inertia('Clients/Create', [
            'teams' => Auth::user()->role_id === Role::ADMIN ? Team::all() 
                        : Auth::user()->ledTeams->transform(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name
            ])
        ]);
    }

    public function store(StoreClientRequest $request)
    {
        $client = Client::create([
            'name' => $request->name,
            'active' => $request->active,
            'user_id' => Auth::id(),
            'team_id' => Auth::user()->role_id !== Role::USER ? $request->team_id : null
        ]);

        if ($request->domain){
            $client->websites()->create([
                'domain' => $request->domain,
                'user_id' => Auth::id()
            ]);
        }
        
        return redirect()->route('clients.index')
                         ->with('success', 'Client saved successfully!');
    }
    
    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->name = $request->name;
        $client->active = $request->active;
        $client->team_id = Auth::user()->role_id !== Role::USER ? $request->team_id : null;

        $client->save();
    
        return redirect()->route('clients.index', ['page' => $request->page,'search'=> $request->keyword])
                         ->with('success', 'Client updated successfully!');
    }

    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()->route('clients.index')
                         ->with('success', 'Client deleted successfully!');
    }
    
    public function getClientProjects(Client $client)
    {
        return $client->projects()->with('website')->get();
    }

    public function getClientWebsites(Client $client)
    {
        return $client->websites;
    }

    private function getClients()
    {
        return Client::filter(request()->only('search'))
                     ->filterByUserRole()
                     ->with('websites:id,client_id,user_id,domain', 'team')
                     ->paginate(10)
                     ->withQueryString()
                     ->through(fn ($client) => [
                        'id' => $client->id,
                        'user_id' => $client->user_id,
                        'name' => $client->name,
                        'created_at' => $client->created_at,
                        'updated_at' => $client->updated_at,
                        'active' => $client->active,
                        'websites' => $client->websites,
                        'team_id' => $client->team_id,
                        'team' => $client->team
                    ]);
    }
}