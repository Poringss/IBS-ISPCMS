@extends('layouts.app')

@section('content')

@php
  $p = request('period', $period ?? '28d');
  $aliases = ['week'=>'28d','month'=>'28d','year'=>'365d'];
  $p = $aliases[$p] ?? $p;

  $titlePeriod = [
      '7d'       => '7 days',
      '28d'      => '28 days',
      '90d'      => '90 days',
      '365d'     => '365 days',
      'lifetime' => 'lifetime',
  ][$p] ?? '28 days';

  $completedTasksFreq   = $completedTasksFreq   ?? collect();
  $finishedProjectsFreq = $finishedProjectsFreq ?? collect();
  $stageLabels        = $stageLabels        ?? [];
  $stageCounts        = $stageCounts        ?? [];
  $labels             = $labels             ?? [];
  $goodCounts         = $goodCounts         ?? [];
  $badCounts          = $badCounts          ?? [];
  $taskTitleLabels    = $taskTitleLabels    ?? [];
  $taskTitleCounts    = $taskTitleCounts    ?? [];
  $projectNameLabels  = $projectNameLabels  ?? [];
  $projectNameCounts  = $projectNameCounts  ?? [];

  $stageSort   = $stageSort   ?? request('stage_sort', 'count_desc');
  $taskSort    = $taskSort    ?? request('task_sort',  'count_desc');
  $projectSort = $projectSort ?? request('project_sort','count_desc');

  $tblSort = $tblSort ?? request('tbl_sort', 'created_at');
  $tblDir  = $tblDir  ?? request('tbl_dir',  'desc');

  $nextDir = function ($col) use ($tblSort, $tblDir) {
      return ($tblSort === $col && $tblDir === 'asc') ? 'desc' : 'asc';
  };
  $sortUrl = function ($col) use ($nextDir) {
      return request()->fullUrlWithQuery(['tbl_sort' => $col, 'tbl_dir' => $nextDir($col)]);
  };
  $dirArrow = function ($col) use ($tblSort, $tblDir) {
      if ($tblSort !== $col) return '';
      return $tblDir === 'asc' ? ' ↑' : ' ↓';
  };
@endphp

<style>
  /* ── Enhanced Dashboard Styles ── */
  .dash-header {
    font-size: 2.4rem;
    font-weight: 800;
    background: linear-gradient(135deg, #22c55e, #4ade80, #86efac);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -0.5px;
  }
  .dash-subtitle {
    color: rgba(255,255,255,0.5);
    font-size: 0.95rem;
    margin-bottom: 1.5rem;
  }

  /* Period toggle */
  .period-toggle .btn {
    border-radius: 20px !important;
    padding: 6px 18px;
    font-weight: 600;
    font-size: 0.85rem;
    border: 1px solid rgba(34,197,94,0.3);
    transition: all 0.3s;
  }
  .period-toggle .btn-outline-light {
    color: rgba(255,255,255,0.6);
    border-color: rgba(255,255,255,0.1);
  }
  .period-toggle .btn-outline-light:hover {
    background: rgba(34,197,94,0.15);
    color: #4ade80;
    border-color: rgba(34,197,94,0.4);
  }
  .period-toggle .btn-primary {
    background: linear-gradient(135deg, #22c55e, #16a34a);
    border: none;
    color: #000;
    box-shadow: 0 4px 15px rgba(34,197,94,0.4);
  }

  /* Metric cards */
  .metric-card {
    background: linear-gradient(145deg, rgba(20,20,30,0.9), rgba(10,10,15,0.95));
    border: 1px solid rgba(34,197,94,0.15);
    border-radius: 20px;
    padding: 1.25rem 1.5rem;
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    flex: 1;
    min-width: 200px;
  }
  .metric-card:hover {
    border-color: rgba(34,197,94,0.4);
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(34,197,94,0.15);
  }
  .metric-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0;
    width: 4px; height: 100%;
    background: linear-gradient(180deg, #22c55e, #4ade80);
    border-radius: 4px 0 0 4px;
  }
  .metric-card .metric-icon {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    display: block;
  }
  .metric-card .metric-label {
    font-size: 0.8rem;
    color: rgba(255,255,255,0.5);
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
  }
  .metric-card .metric-value {
    font-size: 1.6rem;
    font-weight: 800;
    color: #fff;
    margin-top: 2px;
  }

  /* Glass panels */
  .glass-panel {
    background: linear-gradient(145deg, rgba(15,15,22,0.95), rgba(8,8,12,0.98));
    backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 20px;
    padding: 1.5rem;
    transition: border-color 0.3s;
  }
  .glass-panel:hover {
    border-color: rgba(34,197,94,0.2);
  }
  .glass-panel h5 {
    font-weight: 700;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .glass-panel h5::before {
    content: '';
    width: 4px; height: 20px;
    background: #22c55e;
    border-radius: 2px;
    display: inline-block;
  }

  /* Custom selects & inputs */
  .custom-select, .custom-input {
    width: 200px; height: 44px;
    font-size: 0.95rem;
    border-radius: 12px;
    background: rgba(0,0,0,0.5);
    backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,0.08);
    color: #fff;
    padding-right: 0.8rem;
    transition: all 0.3s;
  }
  .custom-select:focus, .custom-input:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(34,197,94,0.3);
    border-color: #22c55e;
  }
  .custom-select {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2322c55e' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592c.859 0 1.319 1.013.753 1.658l-4.796 5.482a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 0.9em;
  }
  .date-wrapper { position: relative; display: inline-block; width: 200px; }
  .calendar-btn {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    background: none; border: none; color: #22c55e; font-size: 1rem; cursor: pointer;
  }

  /* Tables */
  .custom-table {
    background: rgba(0,0,0,0.3);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    overflow: hidden;
  }
  .custom-table thead th {
    background: rgba(34,197,94,0.08) !important;
    color: #4ade80 !important;
    border-color: rgba(255,255,255,0.06) !important;
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.78rem;
    letter-spacing: 0.8px;
    padding: 12px 16px;
  }
  .custom-table tbody td {
    background-color: transparent !important;
    color: #fff !important;
    border-color: rgba(255,255,255,0.04) !important;
    padding: 12px 16px;
  }
  .custom-table tbody tr {
    background-color: transparent !important;
    transition: all 0.2s;
  }
  .custom-table tbody tr:hover {
    background: rgba(34,197,94,0.06) !important;
  }
  .custom-table tr, .custom-table th { background-color: transparent !important; }

  /* Insight panel */
  .insight-card {
    background: linear-gradient(145deg, rgba(15,15,22,0.95), rgba(8,8,12,0.98));
    border: 1px solid rgba(34,197,94,0.12);
    border-radius: 20px;
    padding: 1.5rem;
  }
  .insight-card h5 {
    color: #4ade80;
    font-weight: 700;
    font-size: 1.1rem;
  }
  .insight-card li {
    color: rgba(255,255,255,0.75);
    padding: 6px 0;
    font-size: 0.9rem;
    line-height: 1.5;
  }
  .insight-card li strong {
    color: #4ade80;
  }

  /* Upcoming tasks */
  .task-item {
    background: rgba(0,0,0,0.4);
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 14px;
    padding: 12px 16px;
    margin-bottom: 8px;
    transition: all 0.3s;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .task-item:hover {
    border-color: rgba(34,197,94,0.3);
    background: rgba(34,197,94,0.05);
  }
  .task-item .task-title { color: #fff; font-weight: 600; font-size: 0.9rem; }
  .task-item .task-due { color: rgba(255,255,255,0.4); font-size: 0.8rem; }
  .task-item .btn { border-radius: 10px; padding: 4px 12px; font-size: 0.8rem; }

  /* Filter labels */
  .filter-label {
    color: rgba(255,255,255,0.5);
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: 600;
    margin-bottom: 6px;
  }
</style>

<div class="container mt-4" style="max-width: 1200px;">
  <h2 class="dash-header mb-1">Business Intelligence Dashboard</h2>
  <p class="dash-subtitle">Showing data for the last {{ $titlePeriod }}</p>

  {{-- Period toggle --}}
  <div class="mb-4 period-toggle">
    <div class="btn-group btn-group-sm" role="group" aria-label="Period">
      <a href="{{ request()->fullUrlWithQuery(['period' => '7d']) }}"
         class="btn {{ $p==='7d' ? 'btn-primary' : 'btn-outline-light' }}">7 Days</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '28d']) }}"
         class="btn {{ $p==='28d' ? 'btn-primary' : 'btn-outline-light' }}">28 Days</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '90d']) }}"
         class="btn {{ $p==='90d' ? 'btn-primary' : 'btn-outline-light' }}">90 Days</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => '365d']) }}"
         class="btn {{ $p==='365d' ? 'btn-primary' : 'btn-outline-light' }}">1 Year</a>
      <a href="{{ request()->fullUrlWithQuery(['period' => 'lifetime']) }}"
         class="btn {{ $p==='lifetime' ? 'btn-primary' : 'btn-outline-light' }}">Lifetime</a>
    </div>
  </div>

  {{-- Filters --}}
  <form method="GET" id="filterForm" class="mb-4 glass-panel" style="padding: 1.25rem;">
    <input type="hidden" name="period" value="{{ $p }}">
    <div class="d-flex flex-wrap gap-3 align-items-end">
      <div class="d-flex flex-column">
        <label class="filter-label">Status</label>
        <select name="status" class="form-select form-select-sm text-white custom-select">
          <option value="">All</option>
          <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
          <option value="won"  {{ request('status') == 'won'  ? 'selected' : '' }}>Won</option>
          <option value="lost" {{ request('status') == 'lost' ? 'selected' : '' }}>Lost</option>
        </select>
      </div>
      <div class="d-flex flex-column">
        <label class="filter-label">Stage</label>
        <select name="stage" class="form-select form-select-sm text-white custom-select">
          <option value="">All Stage</option>
          <option value="Prospect"   {{ request('stage') == 'Prospect'   ? 'selected' : '' }}>Prospect</option>
          <option value="Contacted"  {{ request('stage') == 'Contacted'  ? 'selected' : '' }}>Contacted</option>
          <option value="Proposal"   {{ request('stage') == 'Proposal'   ? 'selected' : '' }}>Proposal</option>
          <option value="Closed"     {{ request('stage') == 'Closed'     ? 'selected' : '' }}>Closed</option>
        </select>
      </div>
      <div class="d-flex flex-column">
        <label class="filter-label">From</label>
        <div class="date-wrapper">
          <input type="date" id="fromDate" name="from" class="form-control form-control-sm text-white custom-input" value="{{ request('from') }}">
          <button type="button" class="calendar-btn" onclick="document.getElementById('fromDate').showPicker()"></button>
        </div>
      </div>
      <div class="d-flex flex-column">
        <label class="filter-label">To</label>
        <div class="date-wrapper">
          <input type="date" id="toDate" name="to" class="form-control form-control-sm text-white custom-input" value="{{ request('to') }}">
          <button type="button" class="calendar-btn" onclick="document.getElementById('toDate').showPicker()"></button>
        </div>
      </div>
      <div class="d-flex gap-2 align-self-end">
        <button type="submit" class="btn btn-primary btn-sm">Filter</button>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.1); color: #fff;">Reset</a>
      </div>
    </div>
  </form>

  {{-- Metric Cards --}}
  <div class="d-flex flex-wrap gap-3 mb-4">
    <div class="metric-card">
      <span class="metric-icon">🚀</span>
      <div class="metric-label">Total Leads</div>
      <div class="metric-value">{{ $totalLeads }}</div>
    </div>
    <div class="metric-card">
      <span class="metric-icon">💰</span>
      <div class="metric-label">Total Value</div>
      <div class="metric-value">₱{{ number_format($totalValue, 2) }}</div>
    </div>
    <div class="metric-card">
      <span class="metric-icon">📈</span>
      <div class="metric-label">Conversion Rate</div>
      <div class="metric-value">{{ number_format($conversionRate, 2) }}%</div>
    </div>
    <div class="metric-card">
      <span class="metric-icon">💎</span>
      <div class="metric-label">Lead Conversion</div>
      <div class="metric-value">{{ number_format($conversionRate, 2) }}%</div>
    </div>
  </div>

  <div class="row g-4">
    {{-- LEFT PANEL --}}
    <div class="col-lg-8">
      <div class="d-flex flex-column gap-4">

        {{-- Leads Distribution --}}
        <div class="glass-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Leads Distribution</h5>
            <form method="GET" class="d-inline">
              @foreach(request()->except('stage_sort') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
              @endforeach
              <select name="stage_sort" class="form-select form-select-sm w-auto custom-select" style="width: auto !important; height: 36px; font-size: 0.8rem;" onchange="this.form.submit()">
                <option value="count_desc" {{ ($stageSort ?? '')==='count_desc' ? 'selected' : '' }}>Count ↓</option>
                <option value="count_asc"  {{ ($stageSort ?? '')==='count_asc'  ? 'selected' : '' }}>Count ↑</option>
                <option value="alpha"      {{ ($stageSort ?? '')==='alpha'      ? 'selected' : '' }}>A → Z</option>
                <option value="pipeline"   {{ ($stageSort ?? '')==='pipeline'   ? 'selected' : '' }}>Pipeline</option>
              </select>
            </form>
          </div>
          <canvas id="leadChart" style="width:100%; height:400px;"></canvas>
        </div>

        {{-- Project Results Over Time --}}
        <div class="glass-panel">
          <h5 class="mb-3">Project Results Over Time</h5>
          <canvas id="projectLineChart"></canvas>
        </div>

        {{-- Leads table --}}
        <div class="table-responsive" style="max-height: 400px; overflow-y: auto; border-radius: 16px;">
          <table class="table table-bordered custom-table mb-0">
            <thead>
              <tr>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('name') }}">Name{!! $dirArrow('name') !!}</a></th>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('email') }}">Email{!! $dirArrow('email') !!}</a></th>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('stage') }}">Stage{!! $dirArrow('stage') !!}</a></th>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('status') }}">Status{!! $dirArrow('status') !!}</a></th>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('value') }}">Value{!! $dirArrow('value') !!}</a></th>
                <th><a class="text-decoration-none" style="color: #4ade80;" href="{{ $sortUrl('created_at') }}">Created{!! $dirArrow('created_at') !!}</a></th>
              </tr>
            </thead>
            <tbody>
              @foreach ($leads as $lead)
                <tr>
                  <td>{{ $lead->name }}</td>
                  <td style="color: rgba(255,255,255,0.6) !important;">{{ $lead->email }}</td>
                  <td><span style="background: rgba(34,197,94,0.15); color: #4ade80; padding: 3px 10px; border-radius: 8px; font-size: 0.8rem; font-weight: 600;">{{ $lead->stage }}</span></td>
                  <td>{{ $lead->status }}</td>
                  <td>{{ $lead->value }}</td>
                  <td style="color: rgba(255,255,255,0.5) !important;">{{ $lead->created_at->format('Y-m-d') }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Task Popularity --}}
        <div class="glass-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Task Popularity <span style="color: rgba(255,255,255,0.4); font-size: 0.8rem; font-weight: 400;">(Top per {{ $titlePeriod }})</span></h5>
            <form method="GET" class="d-inline">
              @foreach(request()->except('task_sort') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
              @endforeach
              <select name="task_sort" class="form-select form-select-sm w-auto custom-select" style="width: auto !important; height: 36px; font-size: 0.8rem;" onchange="this.form.submit()">
                <option value="count_desc" {{ ($taskSort ?? '')==='count_desc' ? 'selected' : '' }}>Count ↓</option>
                <option value="count_asc"  {{ ($taskSort ?? '')==='count_asc'  ? 'selected' : '' }}>Count ↑</option>
                <option value="alpha"      {{ ($taskSort ?? '')==='alpha'      ? 'selected' : '' }}>A → Z</option>
              </select>
            </form>
          </div>
          <canvas id="taskPopularityChart" style="max-height: 360px;"></canvas>
          @php $topTaskRow = ($completedTasksFreq instanceof \Illuminate\Support\Collection ? $completedTasksFreq : collect())->first(); @endphp
          <p style="color: rgba(255,255,255,0.5); margin-top: 12px; font-size: 0.9rem;">
            Top task: <strong style="color: #4ade80;">{{ $topTaskRow->title ?? 'None' }}</strong>
            ({{ $topTaskRow->total ?? 0 }})
          </p>
        </div>

        {{-- Project Popularity --}}
        <div class="glass-panel">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Project Popularity <span style="color: rgba(255,255,255,0.4); font-size: 0.8rem; font-weight: 400;">(Top per {{ $titlePeriod }})</span></h5>
            <form method="GET" class="d-inline">
              @foreach(request()->except('project_sort') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
              @endforeach
              <select name="project_sort" class="form-select form-select-sm w-auto custom-select" style="width: auto !important; height: 36px; font-size: 0.8rem;" onchange="this.form.submit()">
                <option value="count_desc" {{ ($projectSort ?? '')==='count_desc' ? 'selected' : '' }}>Count ↓</option>
                <option value="count_asc"  {{ ($projectSort ?? '')==='count_asc'  ? 'selected' : '' }}>Count ↑</option>
                <option value="alpha"      {{ ($projectSort ?? '')==='alpha'      ? 'selected' : '' }}>A → Z</option>
              </select>
            </form>
          </div>
          <canvas id="projectPopularityChart" style="max-height: 360px;"></canvas>
          @php $topProjRow = ($finishedProjectsFreq instanceof \Illuminate\Support\Collection ? $finishedProjectsFreq : collect())->first(); @endphp
          <p style="color: rgba(255,255,255,0.5); margin-top: 12px; font-size: 0.9rem;">
            Top project: <strong style="color: #4ade80;">{{ $topProjRow->name ?? 'None' }}</strong>
            ({{ $topProjRow->total ?? 0 }})
          </p>
        </div>
      </div>
    </div>

    {{-- RIGHT PANEL --}}
    <div class="col-lg-4">
      <div class="d-flex flex-column gap-4">
        {{-- Predictive Insights --}}
        <div class="insight-card">
          <h5>📌 Predictive Insights</h5>
          <ul class="mb-2" style="list-style: none; padding-left: 0;">
            <li>✅ Most leads are in the <strong>{{ $topStageNow ?? 'N/A' }}</strong> stage.</li>
            <li>✅ Lead trend appears <strong>{{ $leadTrend ?? 'stable' }}</strong>.</li>
            <li>✅ Conversion rate of <strong>{{ number_format($conversionRate, 2) }}%</strong> is <strong>{{ $conversionStatusNow ?? 'unknown' }}</strong>.</li>
          </ul>
          <hr style="border-color: rgba(34,197,94,0.15);">
          <ul class="mb-0" style="list-style: none; padding-left: 0;">
            <li>🔮 Next week top stage: <strong>{{ $topStage ?? 'N/A' }}</strong> <small style="color:rgba(255,255,255,0.3);">({{ $topStageConfidence ?? 'low' }})</small></li>
            <li>🔮 New leads: <strong>{{ isset($nextWeekLeads) ? (int)$nextWeekLeads : '—' }}</strong> → <strong>{{ $leadTrend ?? 'stable' }}</strong> <small style="color:rgba(255,255,255,0.3);">({{ $leadTrendConfidence ?? 'low' }})</small></li>
            <li>🔮 {{ $projectInsight ?? '—' }} <small style="color:rgba(255,255,255,0.3);">({{ $projectConfidence ?? 'low' }})</small></li>
            <li>🔮 Predicted conversion ~ <strong>{{ isset($predictedConversion) ? number_format($predictedConversion, 2).'%' : '—' }}</strong> → <strong>{{ $conversionStatus ?? 'unknown' }}</strong> <small style="color:rgba(255,255,255,0.3);">({{ $conversionConfidence ?? 'low' }})</small></li>
          </ul>
        </div>

        {{-- Upcoming Tasks --}}
        <div class="glass-panel">
          <h5 class="mb-3">📅 Upcoming Tasks</h5>
          <div style="max-height: 400px; overflow-y: auto;">
            @forelse ($upcomingTasks as $task)
              <div class="task-item">
                <div>
                  <div class="task-title">{{ $task->title }}</div>
                  <div class="task-due">Due: {{ \Carbon\Carbon::parse($task->due_date)->format('M d, Y') }}</div>
                </div>
                <div class="d-flex gap-2">
                  <a href="{{ route('tasks.edit', $task->id) }}" class="btn btn-sm" style="background: rgba(234,179,8,0.15); color: #facc15; border: 1px solid rgba(234,179,8,0.3);">✏️</a>
                  <a href="{{ route('tasks.show', $task->id) }}" class="btn btn-sm" style="background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3);">👁️</a>
                </div>
              </div>
            @empty
              <div class="task-item" style="justify-content: center;">
                <span style="color: rgba(255,255,255,0.4);">No upcoming tasks.</span>
              </div>
            @endforelse
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Scripts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
Chart.defaults.color = 'rgba(255,255,255,0.6)';
Chart.defaults.font.size = 12;
Chart.defaults.font.family = "'Inter', sans-serif";
Chart.defaults.plugins.legend.labels.color = 'rgba(255,255,255,0.6)';

function pairAndSort(labels, values, mode, pipelineOrder) {
  const pairs = labels.map((l,i)=>({l, v:Number(values[i] ?? 0)}));
  switch (mode) {
    case 'count_asc':  pairs.sort((a,b)=> a.v-b.v); break;
    case 'alpha':      pairs.sort((a,b)=> String(a.l).localeCompare(String(b.l))); break;
    case 'pipeline':
      if (pipelineOrder && pipelineOrder.length) {
        const idx = x => { const i = pipelineOrder.indexOf(String(x ?? '')); return i === -1 ? 9999 : i; };
        pairs.sort((a,b)=> idx(a.l)-idx(b.l));
      }
      break;
    default: pairs.sort((a,b)=> b.v-a.v); break;
  }
  return {labels: pairs.map(p=>p.l), values: pairs.map(p=>p.v)};
}

const stageSort   = @json($stageSort);
const taskSort    = @json($taskSort);
const projectSort = @json($projectSort);
const PIPELINE_ORDER = ['Prospect','Contacted','Proposal','Closed'];

// Leads Distribution
(() => {
  const ctx = document.getElementById('leadChart')?.getContext('2d');
  if (!ctx) return;
  const sorted = pairAndSort(@json($stageLabels ?? []), @json($stageCounts ?? []), stageSort, PIPELINE_ORDER);
  const g = ctx.createLinearGradient(0, 0, 0, 400);
  g.addColorStop(0, 'rgba(34,197,94,0.7)');
  g.addColorStop(1, 'rgba(34,197,94,0.1)');
  new Chart(ctx, {
    type: 'bar',
    data: { labels: sorted.labels, datasets: [{ label: 'Leads by Stage', data: sorted.values, backgroundColor: g, borderColor: '#22c55e', borderWidth: 2, borderRadius: 12 }]},
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }, y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } } } }
  });
})();

// Project Results Over Time
(() => {
  const ctx = document.getElementById('projectLineChart')?.getContext('2d');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: @json($labels ?? []),
      datasets: [
        { label: 'Good Projects', data: @json($goodCounts ?? []), borderColor: '#22c55e', backgroundColor: 'rgba(34,197,94,0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#22c55e', pointBorderWidth: 0, pointRadius: 4 },
        { label: 'Bad Projects',  data: @json($badCounts  ?? []), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)', fill: true, tension: 0.4, pointBackgroundColor: '#ef4444', pointBorderWidth: 0, pointRadius: 4 }
      ]
    },
    options: { responsive: true, scales: { x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }, y: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } } } }
  });
})();

// Task Popularity
(() => {
  const ctx = document.getElementById('taskPopularityChart')?.getContext('2d');
  if (!ctx) return;
  const sorted = pairAndSort(@json($taskTitleLabels ?? []), @json($taskTitleCounts ?? []), taskSort);
  const g = ctx.createLinearGradient(0, 0, 0, 360);
  g.addColorStop(0, 'rgba(74,222,128,0.7)');
  g.addColorStop(1, 'rgba(34,197,94,0.15)');
  new Chart(ctx, {
    type: 'bar',
    data: { labels: sorted.labels, datasets: [{ label: 'Completed', data: sorted.values, backgroundColor: g, borderColor: '#4ade80', borderWidth: 2, borderRadius: 12 }]},
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }, y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } } } }
  });
})();

// Project Popularity
(() => {
  const ctx = document.getElementById('projectPopularityChart')?.getContext('2d');
  if (!ctx) return;
  const sorted = pairAndSort(@json($projectNameLabels ?? []), @json($projectNameCounts ?? []), projectSort);
  const g = ctx.createLinearGradient(0, 0, 0, 360);
  g.addColorStop(0, 'rgba(134,239,172,0.7)');
  g.addColorStop(1, 'rgba(34,197,94,0.1)');
  new Chart(ctx, {
    type: 'bar',
    data: { labels: sorted.labels, datasets: [{ label: 'Finished', data: sorted.values, backgroundColor: g, borderColor: '#86efac', borderWidth: 2, borderRadius: 12 }]},
    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } }, y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.04)' }, ticks: { color: 'rgba(255,255,255,0.5)' } } } }
  });
})();
</script>
@endsection
