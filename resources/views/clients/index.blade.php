@extends('layouts.app')
@section('content')
@php
    $sort = $sort ?? request('sort', 'name');
    $dir  = $dir  ?? request('dir',  'asc');
    $nextDir = function (string $col) use ($sort, $dir) { return ($sort === $col && $dir === 'asc') ? 'desc' : 'asc'; };
    $sortUrl = function (string $col) use ($nextDir) { return request()->fullUrlWithQuery(['sort' => $col, 'dir' => $nextDir($col)]); };
    $arrow = function (string $col) use ($sort, $dir) { if ($sort !== $col) return ''; return $dir === 'asc' ? ' ↑' : ' ↓'; };
@endphp

<div class="container mt-4" style="max-width:1000px;">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h2 class="text-white mb-0">Clients</h2>
    <div class="d-flex align-items-center gap-2">
      <form method="GET" class="d-flex gap-2" style="max-width:340px;">
        <input type="hidden" name="sort" value="{{ $sort }}">
        <input type="hidden" name="dir"  value="{{ $dir }}">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name or email…">
        @if(request('q'))
          <a href="{{ request()->fullUrlWithQuery(['q' => null]) }}" class="btn btn-secondary btn-sm">Clear</a>
        @endif
        <button type="submit" class="btn btn-outline-light btn-sm">Search</button>
      </form>
      <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">+ Add Client</a>
    </div>
  </div>

  @if ($clients->isEmpty())
    <div class="bubble p-4 text-center" style="color:rgba(255,255,255,0.4);">No clients yet.</div>
  @else
    <div class="table-responsive" style="max-height:700px; overflow-y:auto;">
      <table class="table table-bordered custom-table mb-0">
        <thead>
          <tr>
            <th><a class="link-light text-decoration-none" href="{{ $sortUrl('name') }}">Name{!! $arrow('name') !!}</a></th>
            <th><a class="link-light text-decoration-none" href="{{ $sortUrl('email') }}">Email{!! $arrow('email') !!}</a></th>
            <th style="width:130px;"><a class="link-light text-decoration-none" href="{{ $sortUrl('budget') }}">Budget{!! $arrow('budget') !!}</a></th>
            <th><a class="link-light text-decoration-none" href="{{ $sortUrl('created_at') }}">Created{!! $arrow('created_at') !!}</a></th>
            <th><a class="link-light text-decoration-none" href="{{ $sortUrl('updated_at') }}">Updated{!! $arrow('updated_at') !!}</a></th>
            <th style="width:140px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($clients as $client)
            <tr>
              <td>{{ $client->name }}</td>
              <td>{{ $client->email }}</td>
              <td>@php $amt = $client->budget; @endphp {{ is_null($amt) ? '—' : number_format($amt, 2) }}</td>
              <td>{{ optional($client->created_at)->format('Y-m-d') }}</td>
              <td>{{ optional($client->updated_at)->format('Y-m-d') }}</td>
              <td class="text-nowrap">
                <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-warning btn-sm">Edit</a>
                <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Delete this client?')">Delete</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
      @if (method_exists($clients, 'links')) <div class="mt-2">{{ $clients->links() }}</div> @endif
    </div>
  @endif
</div>
@endsection
