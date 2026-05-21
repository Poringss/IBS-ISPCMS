<?php

namespace App\Http\Controllers;

use App\Models\TaskAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
    public function show(TaskAttachment $attachment)
    {
        // 1) Who's viewing?
        $user = Auth::user();
        abort_unless($user, 401);

        // 2) Authorize: allow admin/staff or the owning client
    $role = strtolower(trim((string) ($user->role ?? '')));
    

    // Allow if: admin/staff OR the owning client of the task
    $isAdminOrStaff = (
        (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('staff')))
        || in_array($role, ['admin', 'staff'], true)
    );
        $task = $attachment->task; // ensure TaskAttachment has task() -> belongsTo(Task::class)
        $isOwningClient = $task && (int) $task->client_user_id === (int) $user->id;

        abort_unless($isAdminOrStaff || $isOwningClient, 403);

        // 3) Normalize path and stream from public disk
        $path = ltrim(preg_replace('#^public/#', '', $attachment->path ?? ''), '/');
        $disk = Storage::disk('public');
        abort_unless($disk->exists($path), 404);

        // 4) Stream inline
        return $disk->response($path, $attachment->original_name ?? basename($path));
    }
}
