<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $allowed = ['name', 'contact', 'stage', 'status', 'value', 'created_at', 'updated_at'];

        $sort = $request->get('sort', 'created_at');
        $dir  = $request->get('dir',  'desc');
        if (!in_array($sort, $allowed, true))  $sort = 'created_at';
        if (!in_array(strtolower($dir), ['asc','desc'], true)) $dir = 'desc';

        $q = trim((string) $request->get('q', ''));

        $leads = Lead::query()
            ->when($q !== '', function ($query) use ($q) {
                $term = str_replace(['%','_'], ['\%','\_'], $q);
                $like = "%{$term}%";
                $query->where(function ($qq) use ($like) {
                    $qq->where('name', 'like', $like)
                       ->orWhere('contact', 'like', $like);
                });
            })
            ->orderBy($sort, $dir)
            ->get();

        return view('leads.index', compact('leads', 'sort', 'dir', 'q'));
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'stage'   => 'required|string',
            'value'   => 'required|numeric|min:0',
            'status'  => 'required|string|in:open,won,lost',
        ]);

        // Normalize on write (Title Case stage, lowercase status)
        $validated['contact'] = strtolower(trim($validated['contact']));
        $validated['stage']   = ucfirst(strtolower(trim($validated['stage'])));
        $validated['status']  = strtolower(trim($validated['status']));

        Lead::create([
            'name'    => trim($validated['name']),
            'contact' => $validated['contact'],
            'stage'   => $validated['stage'],
            'value'   => $validated['value'],
            'status'  => $validated['status'],
        ]);

        return redirect()->route('leads.index')->with('success', 'Lead added.');
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'required|email|regex:/^[a-zA-Z0-9._%+-]+@gmail\.com$/',
            'stage'   => 'required|string',
            'value'   => 'required|numeric|min:0',
            'status'  => 'required|string|in:open,won,lost',
        ]);

        // Normalize on write (Title Case stage, lowercase status)
        $validated['contact'] = strtolower(trim($validated['contact']));
        $validated['stage']   = ucfirst(strtolower(trim($validated['stage'])));
        $validated['status']  = strtolower(trim($validated['status']));

        return DB::transaction(function () use ($lead, $validated) {
            // Old state BEFORE update (case-insensitive via model helper)
            $wasClosedWon = method_exists($lead, 'shouldBecomeClient')
                ? $lead->shouldBecomeClient()
                : (strtolower((string)$lead->stage) === 'closed' && strtolower((string)$lead->status) === 'won');

            // Update allowed fields
            $lead->fill([
                'name'    => trim($validated['name']),
                'contact' => $validated['contact'],
                'stage'   => $validated['stage'],
                'value'   => $validated['value'],
                'status'  => $validated['status'],
            ])->save();

            // Ensure we see normalized values/mutators
            $lead->refresh();

            // New state AFTER update
            $isClosedWon = method_exists($lead, 'shouldBecomeClient')
                ? $lead->shouldBecomeClient()
                : (strtolower((string)$lead->stage) === 'closed' && strtolower((string)$lead->status) === 'won');

            // Auto-convert ONLY on transition to Closed + Won
            if (!$wasClosedWon && $isClosedWon) {
                $client = Client::updateOrCreate(
                    ['email' => $lead->contact], // already lowercased
                    ['name' => trim($lead->name), 'budget' => (float) $lead->value]
                );

                // Link lead -> client (uses FK directly; works regardless of relation config)
                $lead->client_id = $client->id;
                $lead->save();
            }

            return redirect()->route('leads.index')->with('success', 'Lead updated.');
        });
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    public function chart()
    {
        $data = Lead::select('stage', \DB::raw('count(*) as total'))
                    ->groupBy('stage')
                    ->get();

        $labels = $data->pluck('stage');
        $totals = $data->pluck('total');

        return view('leads.chart', compact('labels', 'totals'));
    }
}
