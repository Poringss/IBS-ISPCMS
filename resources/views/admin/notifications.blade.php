@extends('layouts.app')
@section('content')
<div class="container mt-4" style="max-width:800px;">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="text-white m-0">Notifications</h2>
    <form method="POST" action="{{ route('admin.notifications.readAll') }}">@csrf <button class="btn btn-outline-light btn-sm">Mark all as read</button></form>
  </div>
  <div class="d-flex flex-column gap-2">
    @forelse($notifications as $n)
      <div class="bubble p-3">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong style="color:#fff;">{{ $n->data['title'] ?? 'Task' }}</strong>
            <span style="color:rgba(255,255,255,0.5);"> — {{ $n->data['message'] ?? '' }}</span>
            @if(!empty($n->data['task_id']))
              <a class="ms-2" href="{{ url('/tasks/'.$n->data['task_id']) }}" style="color:#4ade80;">View</a>
            @endif
          </div>
          <small style="color:rgba(255,255,255,0.3);">{{ $n->created_at->diffForHumans() }}</small>
        </div>
      </div>
    @empty
      <div class="bubble p-4 text-center" style="color:rgba(255,255,255,0.3);">No notifications.</div>
    @endforelse
  </div>
  <div class="mt-3">{{ $notifications->links() }}</div>
</div>
@endsection
