<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\TaskAttachment;

// NEW: for PDF export
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ClientPortalController extends Controller
{
    /**
     * Client dashboard: tasks, recent requests, and related projects
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userId = $user->id;

        // Only tasks tied to this client user; eager-load to avoid N+1
        $tasks = Task::with(['assignedStaff', 'handler', 'attachments'])
            ->where('client_user_id', $userId)
            ->orderBy('due_date')
            ->paginate(10);

        // Recent requests by this client
        $requests = ClientRequest::where('user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        // Projects that have at least one task assigned to this client
        $projectsForClient = Project::query()
            ->whereHas('tasks', function ($q) use ($userId) {
                $q->where('client_user_id', $userId);
            })
            ->with([
                'tasks' => function ($q) use ($userId) {
                    $q->where('client_user_id', $userId)
                      ->select('id','title','status','progress','due_date','project_id');
                }
            ])
            ->orderByDesc('created_at')
            ->get(['id','name','result','created_at']);

        return view('client.index', [
            'tasks'             => $tasks,
            'requests'          => $requests,
            'projectsForClient' => $projectsForClient,
        ]);
    }

    /**
     * Client rates a task (1..5) — only if the task belongs to this client
     */
    public function rateTask(Task $task, Request $request)
    {
        abort_if($task->client_user_id !== $request->user()->id, 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        $task->client_rating = $data['rating'];
        $task->save();

        return back()->with('success', 'Thanks for your rating!');
    }

    /**
     * Client sends a request/message to admin; task_id optional (must belong to the client if provided)
     */
    public function storeRequest(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'task_id' => [
                'nullable',
                Rule::exists('tasks', 'id')->where(fn ($q) => $q->where('client_user_id', $user->id)),
            ],
        ]);

        ClientRequest::create([
            'user_id' => $user->id,
            'task_id' => $data['task_id'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            // status defaults to "open" from the migration
        ]);

        return back()->with('success', 'Request sent to admin.');
    }

    /**
     * EXPORT: download this client's data (tasks + requests) as a PDF
     */
    public function exportPdf(Request $request)
    {
        $user = $request->user();

        // All tasks for this client (no pagination for export)
        $tasks = Task::with(['assignedStaff', 'project', 'handler'])
            ->where('client_user_id', $user->id)
            ->orderBy('due_date')
            ->get();

        // All requests from this client
        $requests = ClientRequest::with('task')
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $now = Carbon::now()->format('Y-m-d H:i:s');

        $pdf = Pdf::loadView('client.pdf', [
            'user'         => $user,
            'tasks'        => $tasks,
            'requests'     => $requests,
            'generated_at' => $now,
        ])->setPaper('a4', 'portrait');

        $filename = 'InsightBlitz_' . preg_replace('/\s+/', '_', $user->name ?? 'client') . '_' . Carbon::now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
        // If you prefer in-browser preview:
        // return $pdf->stream($filename);
    }

public function showAttachment(TaskAttachment $attachment)
{
    $user = Auth::user();

    // Resolve the owning task (and common relationships)
    $task = $attachment->task; // TaskAttachment::belongsTo(Task::class)

    if (!$task) {
        abort(404);
    }

    // Pull common IDs safely (your app already uses these relationships in lists)
    $clientUserId       = (int) ($task->client_user_id ?? 0);
    $assignedStaffId    = (int) optional($task->assignedStaff)->id; // Task::belongsTo(User::class, 'assigned_staff_id') expected
    $handlerId          = (int) optional($task->handler)->id;       // If you have a separate handler relation
    $currentUserId      = (int) $user->id;
    $currentUserRole    = strtolower((string) ($user->role ?? ''));

    // Who can view?
    $isAdmin            = $currentUserRole === 'admin';
    $isAssignedClient   = $clientUserId === $currentUserId;
    $isAssignedStaff    = $assignedStaffId && $assignedStaffId === $currentUserId;
    $isHandler          = $handlerId && $handlerId === $currentUserId;

    if (!($isAdmin || $isAssignedClient || $isAssignedStaff || $isHandler)) {
        // As a fallback, if you sometimes store a plain string "assigned_to"
        // and it holds the staff user's name/email:
        $assignedToStr = trim((string) ($task->assigned_to ?? ''));
        if (
            !($assignedToStr !== '' && (
                strcasecmp($assignedToStr, (string) $user->name)  === 0 ||
                strcasecmp($assignedToStr, (string) $user->email) === 0
            ))
        ) {
            abort(404); // keep opaque for unauthorized users
        }
    }

    // Files are private on the local disk (storage/app/...)
    $disk = 'local';
    if (!Storage::disk($disk)->exists($attachment->path)) {
        abort(404);
    }

    return response()->file(Storage::disk($disk)->path($attachment->path));
}

}
    

