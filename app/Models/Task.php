<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\TaskHandler;
use App\Models\Staff;
use App\Models\Project;
use App\Models\User;
use App\Models\TaskAttachment;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',

        // assignment
        'assigned_staff_id',   // points to staff.id
        'user_id',             // (optional) creator on admin side
        'project_id',

        // client portal
        'client_user_id',      // which client user this task belongs to
        'client_rating',       // 1–5 rating from client

        // progress / completion
        'progress',            // 0–100
        'progress_note',       // optional note from staff
        'completed_at',        // timestamp when marked complete
    ];

    protected $casts = [
        'due_date'      => 'datetime',
        'completed_at'  => 'datetime',
        'client_rating' => 'integer',
        'progress'      => 'integer',
    ];

    /* -------------------- Relationships -------------------- */

    public function handler()
    {
        return $this->hasOne(TaskHandler::class);
    }

    // who the task is assigned to (staff profile)
    public function assignedStaff()
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // the client user this task belongs to (for client portal filtering)
    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    // optional: creator/owner on the admin side
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // uploaded proof files/images
    public function attachments()
    {
        return $this->hasMany(\App\Models\TaskAttachment::class);
    }

    /* -------------------- Helpers (optional) -------------------- */

    // $task->is_complete
    public function getIsCompleteAttribute(): bool
    {
        return ($this->progress ?? 0) >= 100
            || ($this->status === 'done')
            || !is_null($this->completed_at);
    }
}
