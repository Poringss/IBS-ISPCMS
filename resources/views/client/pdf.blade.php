<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Client Report</title>
  <style>
      * { box-sizing: border-box; }
      body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; margin: 24px; }
      h1, h2, h3 { margin: 0 0 6px 0; }
      h1 { font-size: 20px; }
      h2 { font-size: 16px; margin-top: 18px; }
      p { margin: 6px 0; }
      .meta { font-size: 12px; color: #555; margin-bottom: 10px; }

      table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 12px; }
      th, td { border: 1px solid #ccc; padding: 6px 8px; vertical-align: top; }
      th { background: #f2f2f2; text-align: left; }
      .small { font-size: 11px; color: #555; }
      .right { text-align: right; }
      .mt-2 { margin-top: 10px; }
      .mb-2 { margin-bottom: 10px; }
      .muted { color: #777; }
  </style>
</head>
<body>

  <h1>InsightBlitz — Client Data Export</h1>
  <p class="meta">
    Client: <strong>{{ $user->name }}</strong> ({{ $user->email }})<br>
    Generated: {{ $generated_at }}
  </p>

  {{-- TASKS --}}
  <h2>Assigned Tasks</h2>
  @if($tasks->count())
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Title</th>
        <th>Status</th>
        <th>Due Date</th>
        <th>Project</th>
        <th>Assigned Staff</th>
        <th>Handler Rating</th>
        <th>Your Rating</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($tasks as $t)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $t->title ?? '—' }}</td>
          <td>{{ $t->status ?? '—' }}</td>
          <td>
            @if ($t->due_date instanceof \Carbon\Carbon)
              {{ $t->due_date->format('Y-m-d') }}
            @else
              {{ $t->due_date ?? '—' }}
            @endif
          </td>
          <td>{{ $t->project->name ?? '—' }}</td>
          <td>
            {{ $t->assignedStaff->name ?? ($t->assigned_to ?? '—') }}
            @if(!empty($t->assignedStaff?->position))
              <span class="small muted"> — {{ $t->assignedStaff->position }}</span>
            @endif
          </td>
          <td>
            @if($t->handler && !is_null($t->handler->rating))
              {{ $t->handler->rating }} / 5
              @if(!empty($t->handler->recommendation))
                <div class="small muted">{{ $t->handler->recommendation }}</div>
              @endif
            @else
              —
            @endif
          </td>
          <td>{{ $t->client_rating ? ($t->client_rating . ' / 5') : '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
  @else
    <p class="muted">No tasks were found.</p>
  @endif

  {{-- CLIENT REQUESTS --}}
  <h2 class="mt-2">Requests to Admin</h2>
  @if($requests->count())
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>Subject</th>
        <th>Related Task</th>
        <th>Status</th>
        <th>Sent At</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($requests as $r)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $r->subject }}</td>
          <td>{{ $r->task?->title ?? '—' }}</td>
          <td>{{ ucfirst($r->status) }}</td>
          <td>
            @if ($r->created_at instanceof \Carbon\Carbon)
              {{ $r->created_at->format('Y-m-d H:i') }}
            @else
              {{ $r->created_at }}
            @endif
          </td>
        </tr>
        @if(trim($r->message ?? '') !== '')
          <tr>
            <td></td>
            <td colspan="4"><strong>Message:</strong> {{ $r->message }}</td>
          </tr>
        @endif
      @endforeach
    </tbody>
  </table>
  @else
    <p class="muted">No requests were found.</p>
  @endif

</body>
</html>
