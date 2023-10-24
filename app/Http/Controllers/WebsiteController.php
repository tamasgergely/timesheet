<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWebsiteRequest;
use App\Http\Requests\UpdateWebsiteRequest;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;

class WebsiteController extends Controller
{
    public function store(StoreWebsiteRequest $request)
    {
        $client = Client::find($request->client_id);

        $this->authorize('create', [Website::class, $client]);

        Website::create([
            'client_id' => $request->client_id,
            'domain' => $request->domain,
            'user_id' => Auth::id()
        ]);

        return redirect()->route('clients.index')
            ->with('success', 'Website saved successfully!');
    }

    public function update(UpdateWebsiteRequest $request, Website $website)
    {
        $this->authorize('update', $website);

        $website->domain = $request->domain;

        $website->save();

        return redirect()->route('clients.index', ['search'=> $request->keyword])
                         ->with('success', 'Website updated successfully!');

    }

    public function destroy(Website $website)
    {
        $this->authorize('delete', $website);

        $website->delete();

        return redirect()->route('clients.index')
                         ->with('success', 'Website deleted successfully!');
    }
}
