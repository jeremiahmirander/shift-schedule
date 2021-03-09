<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Task;
use App\TaskList;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Task Controller Create
     * Responsible for handling the creation of items
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateTaskRequest $request, TaskList $list)
    {
        $task = new Task($request->all());
        $list->tasks()->save($task);

        return response($task, 201);
    }

    /**
     * Task Controller Update
     * Responsible for handling the updating features of the tasks
     */
    public function update(UpdateTaskRequest $request, TaskList $list, $task_id)
    {
        $task = $list->tasks()->findOrFail($task_id);
        $task->update($request->all());
        return $task;
    }

    /**
     * Task Controller Delete
     * Responsible for handling the deletion of tasks
     */
    public function delete(Request $request, TaskList $list, $task_id)
    {
        $task = $list->tasks()->findOrFail($task_id);

        if ($task->delete()) {
            return response(null, 204);
        }

        return response('bad', 500);
    }
}
