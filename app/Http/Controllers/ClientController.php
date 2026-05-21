<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    // LIST (with sorting + search)
    public function index(Request $request)
    {
        $allowed = ['name', 'email', 'budget', 'created_at', 'updated_at'];
        $sort = $request->get('sort', 'name');
        $dir  = $request->get('dir',  'asc');
        if (!in_array($sort, $allowed, true)) $sort = 'name';
        if (!in_array(strtolower($dir), ['asc','desc'], true)) $dir = 'asc';

        $q = trim((string) $request->get('q', ''));

        $clients = Client::query()
            ->when($q !== '', function ($query) use ($q) {
                $term = str_replace(['%', '_'], ['\%', '\_'], $q);
                $like = "%{$term}%";
                $query->where(fn($qq) => $qq
                    ->where('name',  'like', $like)
                    ->orWhere('email','like', $like)
                );
            })
            ->orderBy($sort, $dir)
            ->get(); // ->paginate(25)->appends(compact('q','sort','dir'))

        return view('clients.index', compact('clients', 'sort', 'dir', 'q'));
    }

    // CREATE FORM
    public function create()
    {
        return view('clients.create');
    }

    // STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => ['required','email','unique:clients,email','regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'],
            'budget' => 'nullable|numeric|min:0',
        ]);

        Client::create([
            'name'   => trim($validated['name']),
            'email'  => strtolower(trim($validated['email'])),
            'budget' => array_key_exists('budget',$validated) && $validated['budget'] !== null
                        ? (float) $validated['budget'] : null,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client added.');
    }

    // SHOW (optional)
    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    // EDIT FORM
    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    // UPDATE
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => ['required','email','unique:clients,email,'.$client->id,'regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/'],
            'budget' => 'nullable|numeric|min:0',
        ]);

        $client->update([
            'name'   => trim($validated['name']),
            'email'  => strtolower(trim($validated['email'])),
            'budget' => array_key_exists('budget',$validated) && $validated['budget'] !== null
                        ? (float) $validated['budget'] : null,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated.');
    }

    // DELETE
    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted.');
    }
}
