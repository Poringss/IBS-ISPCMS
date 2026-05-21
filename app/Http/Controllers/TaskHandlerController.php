<?php
namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskHandler;
use Illuminate\Http\Request;

class TaskHandlerController extends Controller
{
    public function store(Request $request, $task_id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'recommendation' => 'nullable|string',
        ]);

        TaskHandler::create([
            'task_id' => $task_id,
            'rating' => $request->rating,
            'recommendation' => $request->recommendation,
        ]);

        return redirect()->back()->with('success', 'Task has been reviewed.');
    }
}