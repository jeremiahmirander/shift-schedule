<?php

namespace App\Http\Controllers\v1;

use App\Http\Requests\TasklistRequest;
use App\Http\Controllers\Controller;
use App\TaskList;
use Illuminate\Http\Request;

class TaskListController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      return $request->user()->lists;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(TasklistRequest $request)
    {
        $list = new TaskList($request->all());
        $request->user()->lists()->save($list);

        return response($list, 201);
    }


    /**
     * Display the specified resource.
     *
     * @param  \App\TaskList  $list
     * @return \Illuminate\Http\Response
     */
    public function show(TaskList $list)
    {
        return $list->load('tasks');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TaskList  $list
     * @return \Illuminate\Http\Response
     */
    public function update(TasklistRequest $request, TaskList $list)
    {
        $list->update($request->all());

        return $list;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TaskList  $list
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskList $list)
    {
        $list->delete();

        return response(null, 204);
    }
}
