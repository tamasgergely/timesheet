<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Clients/Index', [
            'clients' => $this->getClients(),
            'keyword' => $request->search
        ]);
    }

    public function create()
    {
        return inertia('Clients/Create', [
            'teams' => Auth::user()->teams->transform(fn ($team) => [
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
            'team_id' => $request->team_id
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
    
    public function edit(Request $request, Client $client)
    {
        $this->authorize('update', $client);
        
        $client->website = $client->websites()->first();

        return inertia('Clients/Index', [
            'keyword' => $request->search,
            'client' => $client,
            'clients' => $this->getClients(),
            'teams' => Auth::user()->teams->transform(fn ($team) => [
                'id' => $team->id,
                'name' => $team->name
                ])
        ]);
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->name = $request->name;
        $client->active = $request->active;
        $client->team_id = $request->team_id;

        $client->save();

        if ($request->domain) {
            $website = $client->websites->first();

            if ($website){
                $website->update([
                    'domain' => $request->domain,
                    'user_id' => Auth::id()
                ]);
            }else{
                Website::create([
                    'client_id' => $client->id,
                    'domain' => $request->domain,
                    'user_id' => Auth::id()
                ]);
            }
        }
    
        return redirect()->route('clients.index', ['search'=> $request->keyword])
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
                     ->with('websites:id,client_id,user_id,domain')
                     ->paginate(10)
                     ->withQueryString()
                     ->through(fn ($client) => [
                        'id' => $client->id,
                        'user_id' => $client->user_id,
                        'name' => $client->name,
                        'created_at' => $client->created_at,
                        'updated_at' => $client->updated_at,
                        'active' => $client->active,
                        'websites' => $client->websites
                    ]);
    }
}
