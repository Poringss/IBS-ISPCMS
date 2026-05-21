<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    // Include user_id so linking to a User actually persists
    protected $fillable = ['name', 'position', 'rating', 'user_id'];

    public function user()
    {
        // explicit fk for clarity
        return $this->belongsTo(User::class, 'user_id');
    }

    // Optional: tasks assigned to this staff profile
    public function tasks()
    {
        return $this->hasMany(\App\Models\Task::class, 'assigned_staff_id');
    }
}
