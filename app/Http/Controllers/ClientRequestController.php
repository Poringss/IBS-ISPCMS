<?php

namespace App\Http\Controllers;

use App\Models\ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientRequestController extends Controller
{
    /**
     * Admin: list OPEN requests only (tab = open)
     */
    public function index()
    {
        $requests = ClientRequest::query()
            ->where('status', 'open')
            ->orderByDesc('created_at')
            ->paginate(20);

        $tab = 'open'; // helps the blade highlight the correct tab
        return view('admin.requests.index', compact('requests', 'tab'));
    }

    /**
     * Admin: list RESOLVED requests only (tab = history)
     */
    public function history()
    {
        $requests = ClientRequest::query()
            ->where('status', 'resolved')
            ->orderByDesc('created_at')
            ->paginate(20);

        $tab = 'history';
        return view('admin.requests.index', compact('requests', 'tab'));
    }

    /**
     * Admin: view a single request (no auto write)
     */
    public function show(ClientRequest $requestItem)
    {
        // pass as $req so your blade can keep using $req->
        return view('admin.requests.show', ['req' => $requestItem]);
    }

    /**
     * Admin: update status (must match DB CHECK: open|resolved)
     */
    public function update(Request $request, ClientRequest $requestItem)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['open', 'resolved'])],
        ]);

        $requestItem->update(['status' => $data['status']]);

        return redirect()
            ->route('admin.requests.show', $requestItem)
            ->with('success', 'Request status updated.');
    }
}
