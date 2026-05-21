<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',   // ensure users table has this
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // ok to keep; hashes on set
        ];
    }

    // ───────── Relationships ─────────
    public function staff()
    {
        // explicit fk for clarity
        return $this->hasOne(Staff::class, 'user_id');
    }

    // Optional: tasks for this user via their staff profile
    public function staffTasks()
    {
        // hasManyThrough(Task, Staff, staff.user_id → users.id, tasks.assigned_staff_id → staff.id)
        return $this->hasManyThrough(
            \App\Models\Task::class,
            \App\Models\Staff::class,
            'user_id',             // FK on Staff that refers to users.id
            'assigned_staff_id',   // FK on Task that refers to staff.id
            'id',                  // local key on users
            'id'                   // local key on staff
        );
    }

    // ───────── Role helpers ─────────
    public function isAdmin(): bool { return strtolower($this->role ?? '') === 'admin'; }
    public function isClient(): bool { return strtolower($this->role ?? '') === 'client'; }
    public function isStaff(): bool  { return strtolower($this->role ?? '') === 'staff'; }
}
