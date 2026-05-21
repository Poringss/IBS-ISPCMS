<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

use App\Models\Task;
use App\Models\User;
use App\Models\TaskAttachment;
use App\Notifications\TaskCompleted;

class StaffPortalController extends Controller
{
    /**
     * Staff dashboard: shows tasks assigned to the logged-in staff profile.
     */
    public function dashboard(Request $request)
    {
        $user  = $request->user();
        $staff = $user->staff;

        if (!$staff) {
            return view('staff_portal.dashboard', [
                'user'  => $user,
                'staff' => null,
                'tasks' => collect(),
                'stats' => ['total' => 0, 'open' => 0, 'done' => 0],
            ])->with('warning', 'No Staff profile linked to this account yet.');
        }

        $tasks = Task::where('assigned_staff_id', $staff->id)
            ->latest('id')
            ->paginate(20);

        $row = Task::where('assigned_staff_id', $staff->id)
            ->selectRaw("
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('new','in_progress','blocked') THEN 1 ELSE 0 END) AS open,
                SUM(CASE WHEN status IN ('done','completed') THEN 1 ELSE 0 END) AS done
            ")
            ->first();

        $stats = [
            'total' => (int) ($row->total ?? 0),
            'open'  => (int) ($row->open  ?? 0),
            'done'  => (int) ($row->done  ?? 0),
        ];

        return view('staff_portal.dashboard', compact('user', 'staff', 'tasks', 'stats'));
    }

    /**
     * Staff-only: update progress, optional note, and optionally mark complete.
     * Route: POST /staff/tasks/{task}/progress  (name: staff.tasks.progress)
     */
    public function updateProgress(Request $request, Task $task)
    {
        $user  = $request->user();
        $staff = $user->staff;

        // Only the assigned staff may update
        abort_unless(
            $user->isStaff() && $staff && (int) $task->assigned_staff_id === (int) $staff->id,
            403
        );

        $data = $request->validate([
            'progress' => ['required', 'integer', 'min:0', 'max:100'],
            'note'     => ['nullable', 'string'],
            'complete' => ['nullable', 'boolean'],
        ]);

        // Detect completion transition
        $wasComplete = ($task->progress ?? 0) >= 100
            || ($task->status === 'done')
            || !is_null($task->completed_at);

        $task->progress      = $data['progress'];
        $task->progress_note = $data['note'] ?? null;

        // If explicitly marked complete OR progress is 100, finalize
        if (($data['complete'] ?? false) || (int) $task->progress === 100) {
            $task->completed_at = $task->completed_at ?? now();
            $task->status       = 'done'; // keep consistent with your enums
            $task->progress     = 100;    // normalize to 100 when completed
        }

        $task->save();

        // Notify all admins only if we just transitioned to complete
        $isNowComplete = ($task->progress >= 100) || ($task->status === 'done') || !is_null($task->completed_at);
        if (!$wasComplete && $isNowComplete) {
            $admins = User::where('role', 'admin')->get();
            Notification::send($admins, new TaskCompleted($task));
        }

        return back()->with('success', 'Progress updated.');
    }

    /**
     * Staff-only: upload proof attachments to a task.
     * Route: POST /staff/tasks/{task}/attachments  (name: staff.tasks.attachments.upload)
     */
    public function uploadAttachments(Request $request, Task $task)
    {
        $user  = $request->user();
        $staff = $user->staff;

        // Only the assigned staff may upload
        abort_unless(
            $user->isStaff() && $staff && (int) $task->assigned_staff_id === (int) $staff->id,
            403
        );

        $request->validate([
            'attachments'   => ['required', 'array'],
            'attachments.*' => ['file', 'max:10240', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,txt,zip'],
        ]);

        foreach ($request->file('attachments') as $file) {
            // Store to the public disk so you can link with Storage::url()
            $path = $file->store('task_attachments', 'public');

            TaskAttachment::create([
                'task_id'       => $task->id,
                'uploaded_by'   => $user->id,
                'path'          => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime'          => $file->getClientMimeType(),
                'size'          => $file->getSize(),
            ]);
        }

        return back()->with('success', 'Files uploaded.');
    }
}
