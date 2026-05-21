@extends('layouts.app')
@section('content')
<div class="container mt-4" style="max-width:1000px;">
  <div class="bubble p-4">
    <h3 class="text-white mb-3" style="font-weight:700;">Client Requests</h3>
    <ul class="nav nav-pills mb-3" style="gap:0.5rem;">
      <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'open' ? 'active' : (request()->routeIs('admin.requests.index') ? 'active' : '') }}" href="{{ route('admin.requests.index') }}" style="border-radius:10px;">
          Open <span class="badge bg-secondary ms-1">{{ $openCount ?? 0 }}</span>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ ($activeTab ?? '') === 'history' ? 'active' : (request()->routeIs('admin.requests.history') ? 'active' : '') }}" href="{{ route('admin.requests.history') }}" style="border-radius:10px;">
          History <span class="badge bg-secondary ms-1">{{ $resolvedCount ?? 0 }}</span>
        </a>
      </li>
    </ul>
    <div class="table-responsive">
      <table class="table table-bordered custom-table">
        <thead><tr><th>Subject</th><th>Client</th><th>Related Task</th><th>Status</th><th>Received</th><th style="width:80px;"></th></tr></thead>
        <tbody>
        @forelse ($requests as $r)
          <tr>
            <td>{{ $r->subject }}</td><td>{{ $r->client?->name }} ({{ $r->client?->email }})</td>
            <td>{{ $r->task?->title ?? '—' }}</td><td><span class="badge glass-badge {{ $r->status==='open' ? 'bg-warning text-dark' : 'bg-success' }}">{{ ucfirst($r->status) }}</span></td>
            <td>{{ $r->created_at?->format('Y-m-d H:i') }}</td>
            <td><a href="{{ route('admin.requests.show', $r) }}" class="btn btn-sm btn-primary">View</a></td>
          </tr>
        @empty <tr><td colspan="6" class="text-center" style="color:rgba(255,255,255,0.3);">No requests.</td></tr> @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $requests->links() }}</div>
  </div>
</div>
@push('styles')
<style>
  .nav-pills .nav-link { color: rgba(255,255,255,0.6); background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); font-size: 0.88rem; font-weight: 600; }
  .nav-pills .nav-link.active { background: linear-gradient(135deg,#22c55e,#16a34a) !important; color: #000 !important; border-color: transparent; }
  .nav-pills .nav-link:hover:not(.active) { background: rgba(255,255,255,0.06); color: #fff; }
</style>
@endpush
@endsection
