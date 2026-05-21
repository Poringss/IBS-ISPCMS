@extends('layouts.app')
@section('content')
<div class="container mt-4" style="max-width:580px;">
  <div class="bubble p-4">
    <h2 class="text-white mb-3" style="font-size:1.4rem;">Edit Client</h2>
    <form method="POST" action="{{ route('clients.update', $client->id) }}">
      @csrf @method('PUT')
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input name="name" class="form-control" value="{{ old('name',$client->name) }}" required>
        @error('name') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input name="email" type="email" class="form-control" value="{{ old('email',$client->email) }}" required>
        @error('email') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      </div>
      <div class="mb-3">
        <label class="form-label">Budget (optional)</label>
        <input name="budget" type="number" step="0.01" min="0" class="form-control" value="{{ old('budget',$client->budget) }}">
        @error('budget') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
      </div>
      <div class="d-flex gap-2">
        <button class="btn btn-primary">Update</button>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
@endsection
