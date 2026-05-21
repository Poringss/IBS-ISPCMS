@extends('layouts.app')

@section('content')
<style>
  /* ... Your existing styles stay exactly the same ... */
  .client-header { font-size: 2rem; font-weight: 800; background: linear-gradient(135deg, #22c55e, #4ade80, #86efac); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
  .client-subtitle { color: rgba(255,255,255,0.45); font-size: 0.95rem; margin-bottom: 1.5rem; }
  .client-glass { background: linear-gradient(145deg, rgba(15,15,22,0.95), rgba(8,8,12,0.98)); border: 1px solid rgba(255,255,255,0.06); border-radius: 20px; padding: 1.5rem; transition: border-color 0.3s; }
  .client-glass:hover { border-color: rgba(34,197,94,0.2); }
  .client-glass h5 { font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px; }
  .client-glass h5::before { content: ''; width: 4px; height: 20px; background: #22c55e; border-radius: 2px; }
  .client-table { background: transparent; border-radius: 16px; overflow: hidden; }
  .client-table thead th { background: rgba(34,197,94,0.08) !important; color: #4ade80 !important; border-color: rgba(255,255,255,0.06) !important; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.8px; padding: 12px 14px; }
  .client-table tbody td { background: transparent !important; color: #fff !important; border-color: rgba(255,255,255,0.04) !important; padding: 12px 14px; vertical-align: middle; }
  .progress { background: rgba(255,255,255,0.08); border-radius: 10px; }
  .progress-bar { background: linear-gradient(90deg, #22c55e, #4ade80); font-size: 0.7rem; font-weight: 700; color: #000; }
  .time-card { background: linear-gradient(145deg, rgba(15,15,22,0.95), rgba(8,8,12,0.98)); border: 1px solid rgba(34,197,94,0.15); border-radius: 20px; padding: 1.5rem; position: relative; overflow: hidden; }
  .time-card::before { content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 3px; background: linear-gradient(90deg, #22c55e, #4ade80, transparent); }
  .request-form-card { background: linear-gradient(145deg, rgba(15,15,22,0.95), rgba(8,8,12,0.98)); border: 1px solid rgba(34,197,94,0.12); border-radius: 20px; padding: 1.5rem; }
  .digital-date { font-family: 'Courier New', monospace; font-size: 1rem; color: #fff; background: rgba(0,0,0,0.5); padding: 5px 12px; border-radius: 8px; }
  .digital-clock { font-family: 'Courier New', monospace; font-size: 1.4rem; color: #4ade80; background: rgba(0,0,0,0.5); padding: 5px 12px; border-radius: 8px; text-shadow: 0 0 10px rgba(34,197,94,0.5); }
  .download-btn { background: rgba(34,197,94,0.12); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; border-radius: 10px; padding: 6px 16px; font-size: 0.85rem; font-weight: 600; transition: all 0.3s; text-decoration: none; }
</style>

<div class="container-fluid py-4">
  <div class="row g-4">
    {{-- Left Column: Header and Tasks --}}
    <div class="col-lg-8">
      <h2 class="client-header mb-1">Client Portal</h2>
      <p class="client-subtitle">Welcome! Your portal overview is below.</p>

      <div class="client-glass">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5 class="mb-0">Tasks</h5>
          <a href="{{ route('client.export.pdf') }}" class="download-btn">
            📄 Download PDF
          </a>
        </div>

        <div class="table-responsive" style="max-height: 600px; overflow-y: auto; border-radius: 16px;">
          <table class="table table-bordered client-table mb-0">
            <thead>
              <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Due Date</th>
                <th>Assigned To</th>
                <th>Progress</th>
                <th>Handler Rating</th>
                <th>Your Rating</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($tasks as $task)
                @php
                  $p = (int)($task->progress ?? 0);
                @endphp
                <tr>
                  <td class="fw-semibold">{{ $task->title ?? '—' }}</td>
                  <td>
                    <span style="display:inline-block; padding:3px 12px; border-radius:8px; font-size:0.78rem; font-weight:600;
                      @if(strtolower($task->status ?? '') == 'done' || strtolower($task->status ?? '') == 'completed')
                        background:rgba(34,197,94,0.15); color:#4ade80;
                      @elseif(strtolower($task->status ?? '') == 'in_progress')
                        background:rgba(234,179,8,0.15); color:#facc15;
                      @else
                        background:rgba(59,130,246,0.15); color:#60a5fa;
                      @endif
                    ">{{ $task->status ?? '—' }}</span>
                  </td>
                  <td style="color: rgba(255,255,255,0.6) !important;">
                    {{ !empty($task->due_date) ? (is_object($task->due_date) ? $task->due_date->format('Y-m-d') : $task->due_date) : '—' }}
                  </td>
                  <td>{{ $task->assigned_to ?? ($task->assignedStaff->name ?? '—') }}</td>
                  <td style="min-width:200px;">
                    <div class="progress" style="height:16px;">
                      <div class="progress-bar" role="progressbar" style="width:{{ $p }}%;">
                        {{ $p }}%
                      </div>
                    </div>
                  </td>
                  <td class="text-center">
                    {!! $task->handler_rating ? '<span style="color: #facc15;">'.$task->handler_rating.' ⭐</span>' : '—' !!}
                  </td>
                  <td>
                    @if ($task->client_rating)
                      <span style="color: #4ade80; font-weight: 600;">{{ $task->client_rating }} / 5</span>
                    @else
                      <form method="POST" action="{{ route('client.tasks.rate', $task) }}" class="d-flex gap-2">
                        @csrf
                        <select name="rating" class="form-select form-select-sm" style="background: rgba(0,0,0,0.4); color:#fff; width: 80px;">
                          @for ($i=1; $i<=5; $i++) <option value="{{ $i }}">{{ $i }}</option> @endfor
                        </select>
                        <button class="btn btn-primary btn-sm">Rate</button>
                      </form>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="7" class="text-center" style="color: rgba(255,255,255,0.3); padding: 3rem;">No tasks found.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
        <div class="mt-3">{{ $tasks->links() }}</div>
      </div>
    </div>

    {{-- Right Column: Sidebar --}}
    <div class="col-lg-4">
      <div class="d-flex flex-column gap-4">
        {{-- Live Date & Time --}}
        <div class="time-card d-flex justify-content-between align-items-center">
          <div>
            <h5 class="mb-1" style="color: #fff; font-weight: 700;">Date & Time</h5>
            <div style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">Your local time</div>
          </div>
          <div class="text-end">
            <div id="calendarDate" class="digital-date mb-2"></div>
            <div id="liveClock" class="digital-clock"></div>
          </div>
        </div>

        {{-- Request to Admin --}}
        <div class="request-form-card">
          <h5 class="mb-3" style="color: #fff; font-weight: 700; display: flex; align-items: center; gap: 8px;">
            <span style="width: 4px; height: 20px; background: #22c55e; border-radius: 2px; display: inline-block;"></span>
            Request to Admin
          </h5>
          
          <form method="POST" action="{{ route('client.requests.store') }}">
            @csrf
            <div class="mb-3">
              <label class="form-label text-white-50 small">SUBJECT</label>
              <input class="form-control bg-dark text-white border-secondary" name="subject" required>
            </div>
            <div class="mb-3">
              <label class="form-label text-white-50 small">RELATED TASK (OPTIONAL)</label>
              <select name="task_id" class="form-select bg-dark text-white border-secondary">
                <option value="">— None —</option>
                @foreach ($tasks as $t)
                  <option value="{{ $t->id }}">{{ $t->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label text-white-50 small">MESSAGE</label>
              <textarea class="form-control bg-dark text-white border-secondary" name="message" rows="4" required></textarea>
            </div>
            <button class="btn btn-success w-100 fw-bold" style="background: #22c55e; border: none;">Send Request</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function updateClock() {
  const now = new Date();
  const dateEl = document.getElementById('calendarDate');
  const clockEl = document.getElementById('liveClock');
  if (dateEl) dateEl.textContent = now.toLocaleDateString('en-US', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
  if (clockEl) clockEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}
updateClock();
setInterval(updateClock, 1000);
</script>
@endsection