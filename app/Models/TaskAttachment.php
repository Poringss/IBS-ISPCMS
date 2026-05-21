<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    protected $fillable = [
        'task_id', 'uploaded_by', 'path', 'original_name', 'mime', 'size',
    ];

    public function task()
{
    return $this->belongsTo(\App\Models\Task::class);
}

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    
}
