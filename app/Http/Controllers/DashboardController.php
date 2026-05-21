<?php

namespace App\Http\Controllers;

use App\Services\InsightEngine;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\Lead;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;   // ← add this

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        
        // ----------------------------
        // SORT PARAMS (NEW)
        // ----------------------------
        $stageSort   = $request->get('stage_sort', 'count_desc');
        $taskSort    = $request->get('task_sort', 'count_desc');
        $projectSort = $request->get('project_sort', 'count_desc');

        // Leads table sort (server-side)
        $tblSort = $request->get('tbl_sort', 'created_at');
        $tblDir  = $request->get('tbl_dir', 'desc');
        $allowedCols = ['name','stage','status','value','created_at'];
        if (!in_array($tblSort, $allowedCols, true)) $tblSort = 'created_at';
        $tblDir = $tblDir === 'asc' ? 'asc' : 'desc';

        // Generic sorter for (labels, counts)
        $sortPairs = function(array $labels, array $counts, string $mode, ?array $pipelineOrder = null) {
            $pairs = collect($labels)->zip($counts)->map(fn($z) => ['label'=>$z[0], 'count'=>$z[1]]);
            switch ($mode) {
                case 'count_asc':
                    $pairs = $pairs->sortBy('count');
                    break;
                case 'alpha':
                    $pairs = $pairs->sortBy(fn($r) => mb_strtolower((string)$r['label']));
                    break;
                case 'pipeline':
                    $rank = collect($pipelineOrder ?? [])->flip(); // label => index
                    $pairs = $pairs->sortBy(fn($r) => $rank[$r['label']] ?? 9999);
                    break;
                default: // count_desc
                    $pairs = $pairs->sortByDesc('count');
            }
            return [
                'labels' => $pairs->pluck('label')->values()->all(),
                'counts' => $pairs->pluck('count')->values()->all(),
            ];
        };

        // ----------------------------
        // PERIOD (YouTube-style): 7d | 28d | 90d | 365d | lifetime
        // ----------------------------
        $period = strtolower($request->get('period', '28d'));
        if (!in_array($period, ['7d','28d','90d','365d','lifetime'], true)) {
            $period = '28d';
        }

        $makeDailyWindow = function (int $days) {
            return [
                'strftime'     => "%Y-%m-%d",
                'windowStart'  => Carbon::now()->subDays($days - 1)->startOfDay(),
                'stepFn'       => fn($c) => $c->addDay(),
                'labelFn'      => fn($c) => $c->format('Y-m-d'),
                'rangeStartFn' => fn($c) => $c->copy()->startOfDay(),
                'rangeEndFn'   => fn($c) => $c->copy()->endOfDay(),
            ];
        };

        if ($period === 'lifetime') {
            $strftime = "%Y-%m";

            $minLead    = \App\Models\Lead::min('created_at');
            $minProject = \App\Models\Project::min('created_at');
            $minTask    = \App\Models\Task::min('updated_at');

            $globalStart = collect([$minLead, $minProject, $minTask])
                ->filter(fn($d) => !is_null($d))
                ->min();

            $windowStart = $globalStart
                ? Carbon::parse($globalStart)->startOfMonth()
                : Carbon::now()->startOfMonth();

            $stepFn       = fn($c) => $c->addMonth();
            $labelFn      = fn($c) => $c->format('Y-m');
            $rangeStartFn = fn($c) => $c->copy()->startOfMonth();
            $rangeEndFn   = fn($c) => $c->copy()->endOfMonth();
        } else {
            $cfg = match ($period) {
                '7d'   => $makeDailyWindow(7),
                '28d'  => $makeDailyWindow(28),
                '90d'  => $makeDailyWindow(90),
                '365d' => $makeDailyWindow(365),
            };

            $strftime     = $cfg['strftime'];
            $windowStart  = $cfg['windowStart'];
            $stepFn       = $cfg['stepFn'];
            $labelFn      = $cfg['labelFn'];
            $rangeStartFn = $cfg['rangeStartFn'];
            $rangeEndFn   = $cfg['rangeEndFn'];
        }

        // Build display labels + starts
        $labels       = collect();
        $periodStarts = collect();
        $cur = $windowStart->copy();
        while ($cur <= now()) {
            $labels->push($labelFn($cur));
            $periodStarts->push($cur->copy());
            $stepFn($cur);
        }
        if ($labels->isEmpty()) {
            $labels->push($labelFn(now()));
            $periodStarts->push(now());
        }
        $finalEnd = $rangeEndFn($periodStarts->last()->copy());

        // For these presets, the strftime key == display label
        $labelKeysForSql = $labels->values();

        // ----------------------------
        // LEADS (respect window unless lifetime or explicit from/to)
        // ----------------------------
        $query = Lead::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }

        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('created_at', [$request->from, $request->to]);
        } elseif ($period !== 'lifetime') {
            $query->whereBetween('created_at', [$windowStart, $finalEnd]);
        }

        // Table sort (NEW)
        $query->orderBy($tblSort, $tblDir);

        $leads = $query->get();
        $noData = $leads->isEmpty();

        $totalLeads = $leads->count();
        $totalValue = $leads->sum('value');
        $conversionRate = $totalLeads ? $leads->where('status', 'won')->count() / $totalLeads * 100 : 0;

        // Stage distribution
        $chartData   = $leads->groupBy('stage')->map(fn ($g) => $g->count());
        $stageLabels = $chartData->keys()->map(fn($s) => $s ?? 'N/A')->values()->toArray();
        $stageCounts = $chartData->values()->toArray();

        // Apply sorting to stage bars (NEW)
        $pipelineOrder = ['Prospect','Contacted','Proposal','Closed'];
        $sortedStages = $sortPairs($stageLabels, $stageCounts, $stageSort, $pipelineOrder);
        $stageLabels = $sortedStages['labels'];
        $stageCounts = $sortedStages['counts'];

        // Leads time-series
        $leadsByPeriodRaw = Lead::selectRaw("strftime('$strftime', created_at) as bucket, COUNT(*) as total")
            ->whereBetween('created_at', [$windowStart, $finalEnd])
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket');

        $leadCountsByPeriod = $labelKeysForSql
            ->map(fn($key) => (int)($leadsByPeriodRaw[$key] ?? 0))
            ->values();

        // Projects good/bad time-series
        $projectBuckets = Project::selectRaw("strftime('$strftime', created_at) as bucket, result, COUNT(*) as total")
            ->whereBetween('created_at', [$windowStart, $finalEnd])
            ->groupBy('bucket', 'result')
            ->orderBy('bucket')
            ->get();

        $goodMap = $projectBuckets->where('result', 'good')->keyBy('bucket');
        $badMap  = $projectBuckets->where('result', 'bad')->keyBy('bucket');

        $goodCounts = $labelKeysForSql->map(fn($key) => (int) optional($goodMap->get($key))->total)->values();
        $badCounts  = $labelKeysForSql->map(fn($key) => (int) optional($badMap->get($key))->total)->values();

        // --------------- Popularity (current window) ---------------
        $completedTasksFreq = Task::select('title', DB::raw('COUNT(*) as total'))
            ->where('status', 'completed')
            ->whereBetween('updated_at', [$windowStart, $finalEnd])
            ->groupBy('title')
            ->orderByDesc('total')
            ->get();

        $finishedProjectsFreq = Project::select('name', DB::raw('COUNT(*) as total'))
            ->whereNotNull('result')
            ->whereBetween('created_at', [$windowStart, $finalEnd])
            ->groupBy('name')
            ->orderByDesc('total')
            ->get();

        // Labels + counts for charts
        $taskTitleLabels   = $completedTasksFreq->pluck('title')->map(fn($v) => $v ?: 'Untitled')->take(50)->values()->all();
        $taskTitleCounts   = $completedTasksFreq->pluck('total')->take(50)->values()->all();

        $projectNameLabels = $finishedProjectsFreq->pluck('name')->map(fn($v) => $v ?: 'Untitled')->take(50)->values()->all();
        $projectNameCounts = $finishedProjectsFreq->pluck('total')->take(50)->values()->all();

        // Apply sorting to popularity bars (NEW)
        $sortedTasks = $sortPairs($taskTitleLabels, $taskTitleCounts, $taskSort);
        $taskTitleLabels = $sortedTasks['labels'];
        $taskTitleCounts = $sortedTasks['counts'];

        $sortedProjects = $sortPairs($projectNameLabels, $projectNameCounts, $projectSort);
        $projectNameLabels = $sortedProjects['labels'];
        $projectNameCounts = $sortedProjects['counts'];

        // Upcoming tasks
        $upcomingTasks = Task::whereNotNull('due_date')
            ->whereDate('due_date', '>=', now())
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // =========================
        // PREDICTIVE INSIGHTS
        // =========================
        $engine = new InsightEngine();

        // ---------- LEADS ----------
        $weeklyLeads = DB::table('leads')
            ->selectRaw("strftime('%Y-%W', created_at) as yw, COUNT(*) as c")
            ->where('created_at', '>=', Carbon::now()->subWeeks(8)->startOfWeek())
            ->groupBy('yw')->orderBy('yw')->pluck('c')->toArray();

        $lastWeekLeads  = count($weeklyLeads) ? (float) end($weeklyLeads) : 0.0;
        $leadForecast   = $engine->forecastWithR2($weeklyLeads, 1);
        $nextWeekLeads  = $leadForecast['values'][0] ?? 0.0;
        $leadTrend      = $engine->classifyTrend($lastWeekLeads, $nextWeekLeads, 0.10);

        $leadTrendConfidence = $leadForecast['r2'] >= 0.65 ? 'high'
            : ($leadForecast['r2'] >= 0.35 ? 'medium' : 'low');

        // “Now” top stage vs “Next” top stage
        $topStageNow = optional($chartData)->sortDesc()->keys()->first() ?? 'N/A';

        $stageCountsQ = DB::table('leads')
            ->selectRaw("stage, strftime('%Y-%W', created_at) as yw, COUNT(*) as c")
            ->whereNotNull('stage')
            ->where('created_at', '>=', Carbon::now()->subWeeks(6)->startOfWeek())
            ->groupBy('stage', 'yw')->orderBy('stage')->get();

        $byStage = [];
        foreach ($stageCountsQ as $row) {
            $byStage[$row->stage] = $byStage[$row->stage] ?? [];
            $byStage[$row->stage][] = (int) $row->c;
        }
        $stageForecasts = [];
        foreach ($byStage as $stage => $series) {
            $stageForecasts[$stage] = $engine->forecastWithR2($series, 1);
        }
        $topStage = 'N/A'; $topStageR2 = 0.0;
        if ($stageForecasts) {
            $ranked = [];
            foreach ($stageForecasts as $stage => $res) {
                $ranked[$stage] = $res['values'][0] ?? 0.0;
            }
            arsort($ranked);
            $topStage = array_key_first($ranked) ?? 'N/A';
            $topStageR2 = $stageForecasts[$topStage]['r2'] ?? 0.0;
        }
        $topStageConfidence = $topStageR2 >= 0.65 ? 'high'
            : ($topStageR2 >= 0.35 ? 'medium' : 'low');

        // ---------- PROJECTS ----------
        $weeklyGood = DB::table('projects')
            ->selectRaw("strftime('%Y-%W', created_at) as yw, COUNT(*) as c")
            ->where('result', 'good')
            ->where('created_at', '>=', Carbon::now()->subWeeks(8)->startOfWeek())
            ->groupBy('yw')->orderBy('yw')->pluck('c')->toArray();

        $weeklyBad = DB::table('projects')
            ->selectRaw("strftime('%Y-%W', created_at) as yw, COUNT(*) as c")
            ->where('result', 'bad')
            ->where('created_at', '>=', Carbon::now()->subWeeks(8)->startOfWeek())
            ->groupBy('yw')->orderBy('yw')->pluck('c')->toArray();

        $goodNext  = $engine->forecastWithR2($weeklyGood, 4);
        $badNext   = $engine->forecastWithR2($weeklyBad, 4);

        $sumGood30 = array_sum($goodNext['values']);
        $sumBad30  = array_sum($badNext['values']);

        $projectInsight = $sumGood30 >= $sumBad30
            ? 'Good project outcomes are likely to outnumber bad in the next 30 days'
            : 'Bad project outcomes may outnumber good in the next 30 days';

        $projectConfidence = min($goodNext['r2'], $badNext['r2']) >= 0.65 ? 'high'
            : (min($goodNext['r2'], $badNext['r2']) >= 0.35 ? 'medium' : 'low');

        // ---------- CONVERSION ----------
        $dailyConv = DB::table('leads')
            ->selectRaw("strftime('%Y-%m-%d', created_at) as d,
                         SUM(CASE WHEN status = 'won' THEN 1 ELSE 0 END) as won,
                         COUNT(*) as total")
            ->where('created_at', '>=', Carbon::now()->subDays(45)->startOfDay())
            ->groupBy('d')->orderBy('d')->get();

        $dailyRates = [];
        foreach ($dailyConv as $r) {
            $rate = $r->total ? ($r->won / $r->total) * 100.0 : 0.0;
            $dailyRates[] = round($rate, 2);
        }

        $predRate14 = $engine->forecastWithR2($dailyRates, 14);
        $predictedConversion = count($predRate14['values'])
            ? array_sum($predRate14['values']) / count($predRate14['values'])
            : (count($dailyRates) ? (float) end($dailyRates) : 0.0);

        $conversionStatus = $engine->rateBucket($predictedConversion);
        $conversionConfidence = $predRate14['r2'] >= 0.65 ? 'high'
            : ($predRate14['r2'] >= 0.35 ? 'medium' : 'low');

        $conversionStatusNow = $engine->rateBucket($conversionRate);

        // ----------------------------
        // RETURN
        // ----------------------------
        return view('dashboard.index', [
            // raw
            'leads' => $leads,
            'noData' => $noData,
            'totalLeads' => $totalLeads,
            'totalValue' => $totalValue,
            'conversionRate' => $conversionRate,

            // stage bar chart (sorted)
            'stageLabels' => $stageLabels,
            'stageCounts' => $stageCounts,

            // time-series
            'labels'     => $labels,
            'period'     => $period,
            'goodCounts' => $goodCounts,
            'badCounts'  => $badCounts,
            'leadCountsByPeriod' => $leadCountsByPeriod,

            // popularity (sorted)
            'taskTitleLabels'   => $taskTitleLabels,
            'taskTitleCounts'   => $taskTitleCounts,
            'projectNameLabels' => $projectNameLabels,
            'projectNameCounts' => $projectNameCounts,

            // misc
            'upcomingTasks' => $upcomingTasks,

            // predictive
            'topStageNow' => $topStageNow,
            'topStage'    => $topStage,
            'leadTrend'   => $leadTrend,
            'projectInsight'   => $projectInsight,
            'conversionStatus' => $conversionStatus,
            'conversionStatusNow' => $conversionStatusNow,

            // confidences
            'leadTrendConfidence'  => $leadTrendConfidence,
            'topStageConfidence'   => $topStageConfidence,
            'projectConfidence'    => $projectConfidence,
            'conversionConfidence' => $conversionConfidence,
            'predictedConversion'  => round($predictedConversion, 2),

            // expose sort states to blade
            'stageSort'   => $stageSort,
            'taskSort'    => $taskSort,
            'projectSort' => $projectSort,
            'tblSort'     => $tblSort,
            'tblDir'      => $tblDir,
        ]);
    }

public function notifications(Request $request)
{
    // If you already have a dedicated notifications table/model, swap this block out.
    // For now we derive "notifications" from recently completed tasks.
    $q   = trim((string) $request->get('q', ''));
    $dir = $request->get('dir', 'desc') === 'asc' ? 'asc' : 'desc';

    $rowsQ = \App\Models\Task::query()
        ->select(['id', 'title', 'updated_at'])
        ->where('status', 'completed');

    if ($q !== '') {
        $rowsQ->where(function ($sub) use ($q) {
            $sub->where('title', 'like', "%{$q}%");
        });
    }

    $rows = $rowsQ->orderBy('updated_at', $dir)
        ->limit(200)                // cap for safety
        ->get()
        ->map(function ($t) {
            return (object) [
                'id'         => $t->id,
                'message'    => "Task completed: {$t->title}",
                'created_at' => $t->updated_at ?? $t->created_at,
                'link'       => route('tasks.show', $t->id),
            ];
        });

    return view('notifications.index', [
        'rows' => $rows,
        'q'    => $q,
        'dir'  => $dir,
    ]);
}
}


