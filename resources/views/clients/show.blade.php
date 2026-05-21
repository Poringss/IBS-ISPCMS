@extends('layouts.app')
@section('content')
<div class="container mt-4" style="max-width:580px;">
  <div class="bubble p-4">
    <h2 class="text-white mb-3" style="font-size:1.4rem;">{{ $client->name }}</h2>
    <div class="mb-2" style="color:rgba(255,255,255,0.6);"><strong style="color:rgba(255,255,255,0.8);">Email:</strong> {{ $client->email }}</div>
    <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm mt-3">🔙 Back to List</a>
  </div>
</div>
@endsection
