<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['client_id', 'name', 'result', 'created_at'];
    public $timestamps = false;

    protected $casts = [
        'created_at' => 'datetime',
    ];

public function client()
{
    return $this->belongsTo(Client::class);
}

public function tasks()
{
    return $this->hasMany(\App\Models\Task::class, 'project_id');
}
}


    