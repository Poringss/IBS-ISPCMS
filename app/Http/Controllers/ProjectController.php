<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    /**
     * Projects list with sorting, search, and pagination.
     * ?q=search  | ?sort=name|client_name|result|created_at  | ?dir=asc|desc
     */
public function index(Request $request)
{
    $sort = $request->get('sort', 'created_at');
    $dir  = strtolower($request->get('dir', 'desc')) === 'asc' ? 'asc' : 'desc';
    $q    = trim($request->get('q', ''));

    // Whitelist sort columns (client_name is via join)
    $allowed = ['name', 'client_name', 'result', 'created_at'];

    if (!in_array($sort, $allowed, true)) {
        $sort = 'created_at';
    }

    $query = \DB::table('projects AS pr')
        ->leftJoin('clients AS c', 'c.id', '=', 'pr.client_id')
        ->select('pr.*', \DB::raw('COALESCE(c.name, "") AS client_name'));

    if ($q !== '') {
        $query->where(function ($w) use ($q) {
            $like = "%{$q}%";
            $w->where('pr.name', 'like', $like)
              ->orWhere('pr.result', 'like', $like)
              ->orWhere('c.name', 'like', $like);
        });
    }

    // Order by chosen column (alias works)
    $query->orderBy($sort, $dir);

    // NO pagination – fetch all and let the table scroll
    $projects = $query->get();

    return view('projects.index', [
        'projects' => $projects,
        'sort'     => $sort,
        'dir'      => $dir,
        'q'        => $q,
    ]);
}


    public function create()
    {
        $clients = Client::all();
        return view('projects.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate(
            [
                'client_id'  => 'required|exists:clients,id',
                'name'       => 'required|string',
                'result'     => 'nullable|string',
                'created_at' => 'required|date',
            ],
            [
                // <- custom message should be passed as the second param
                'created_at.required' => 'Please select a creation date.',
            ]
        );

        Project::create([
            'client_id'  => $request->client_id,
            'name'       => $request->name,
            'result'     => $request->result,
            'created_at' => $request->created_at,
            'updated_at' => now(),
        ]);

        return redirect()->route('projects.index')->with('success', 'Project created.');
    }

    public function edit(Project $project)
    {
        $clients = Client::all();
        return view('projects.edit', compact('project', 'clients'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'name'       => 'required|string',
            'result'     => 'nullable|string',
            // uncomment if you want to allow editing the created date:
            // 'created_at' => 'nullable|date',
        ]);

        $project->update([
            'client_id'  => $request->client_id,
            'name'       => $request->name,
            'result'     => $request->result,
            // 'created_at' => $request->created_at ?? $project->created_at,
        ]);

        return redirect()->route('projects.index')->with('success', 'Project updated.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted.');
    }
}
