<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        return inertia('Projects/Index', [
            'projects' => $this->getProjects(),
            'keyword' => $request->search,
            'clients' => $this->getClients()
        ]);
    }

    public function create()
    {
        return inertia('Projects/Create', [
            'clients' => $this->getClients()
        ]);
    }

    public function store(StoreProjectRequest $request)
    {
        $client = Client::find($request->client);
        
        $this->authorize('create', [Project::class, $client]);
        
        Project::create([
            'user_id' => Auth::id(),
            'client_id' => $request->client,
            'website_id' => $request->website ?? null,
            'description' => $request->description,
            'name' => $request->name,
            'active' => $request->active
        ]);

        return redirect()->route('projects.index')
                         ->with('success', 'Project saved successfully!');
    }

    public function update(UpdateProjectRequest $request, Project $project)
    {
        $this->authorize('update', $project);
        
        $project->client_id = $request->client_id;
        $project->website_id = $request->website ?? null;
        $project->name = $request->name;
        $project->description = $request->description;
        $project->active = $request->active;
        
        $project->save();
        
        return redirect()->route('projects.index', ['page' => $request->page,'search'=> $request->keyword])
                         ->with('success', 'Project updated successfully!');
    }

    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        $project->delete();

        return redirect()->back()
                         ->with('success', 'Project deleted successfully!');
    }

    private function getProjects()
    {
        return Project::with(['client', 'website:id,domain'])
                      ->filter(request()->only('search'))
                      ->filterByUserRole()
                      ->paginate(10)
                      ->withQueryString()
                      ->through(fn ($project) => [
                        'id' => $project->id,
                        'name' => $project->name,
                        'client_id' => $project->client->id,
                        'client_name' => $project->client->name ?? '',
                        'description' => $project->description,
                        'active' => $project->active,
                        'website' => $project->website ?? null,
                    ]);
    }

    private function getClients()
    {
        return Client::select('id', 'name')
                ->with('websites:id,domain,client_id')
                ->filterByUserRole()
                ->get();
    }
}
