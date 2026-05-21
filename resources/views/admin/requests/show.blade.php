@extends('layouts.app')
@section('content')
<div class="container mt-4" style="max-width:700px;">
  <h3 class="text-white mb-3" style="font-weight:700;">Client Request</h3>
  @if (session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

  <div class="bubble p-4 mb-3">
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">Subject:</strong> <span class="text-white">{{ $req->subject }}</span></div>
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">From:</strong> <span class="text-white">{{ $req->client->name ?? 'Client' }} ({{ $req->client->email ?? 'unknown' }})</span></div>
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">Related Task:</strong> <span class="text-white">{{ $req->task?->title ?? '—' }}</span></div>
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">Status:</strong> <span class="badge glass-badge {{ $req->status==='open' ? 'bg-warning text-dark' : 'bg-success' }}">{{ ucfirst($req->status) }}</span></div>
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">Sent:</strong> <span class="text-white">{{ $req->created_at->format('Y-m-d H:i') }}</span></div>
    <hr style="border-color:rgba(255,255,255,0.06);">
    <div class="mb-2"><strong style="color:rgba(255,255,255,0.5);">Message</strong></div>
    <div class="p-3" style="background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.06); border-radius:10px; white-space:pre-wrap; color:rgba(255,255,255,0.8);">{{ $req->message }}</div>
  </div>

  <div class="bubble p-4">
    <h5 class="text-white mb-3" style="font-weight:700;">Update Status</h5>
    <form method="POST" action="{{ route('admin.requests.update', $req) }}">
      @csrf @method('PATCH')
      <div class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label">Status</label>
          <select name="status" class="form-select"><option value="open" @selected($req->status==='open')>Open</option><option value="resolved" @selected($req->status==='resolved')>Resolved</option></select>
        </div>
        <div class="col-md-8"><button class="btn btn-primary">Save</button><a href="{{ route('admin.requests.index') }}" class="btn btn-secondary ms-2">Back</a></div>
      </div>
    </form>
  </div>
</div>
@endsection
