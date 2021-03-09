<?php

namespace Tests\Feature;

use App\Task;
use App\TaskList;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * Make sure that the task has been created in db
     *
     * @test
     */
    public function verify_task_creation()
    {
        $user   = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list  = factory(TaskList::class)->make();
        $user->lists()->save($list);

        $request = [
            'name' => $this->faker->words(rand(2, 3), true)
        ];

        $response = $this->postJson(route('v1.list', [$list->getKey()]), $request)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
                'completed',
            ])
            ->assertJson([
                'name' => $request['name'],
            ]);

        $task = Task::find($response->decodeResponseJson('id'));
        $this->assertNotNull($task);
        $this->assertEquals($request['name'], $task->name);
        $this->assertTrue($user->is($task->task_list->user));
    }

    /**
     * Make sure that the task can be editted/changed
     *
     * @test
     */
    public function update_task_name()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $tasklist = factory(TaskList::class)->create([
            'user_id' => $user->getKey(),
        ]);
        $task = factory(Task::class)->create(['list_id' => $tasklist->id]);

        $originalName = $task->name;

        $request = [
            'name' => $this->faker->words(rand(2, 3), true)
        ];

        $response = $this->patchJson(route('v1.task', [$tasklist->getKey(), $task->getKey()]), $request)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'completed',
            ])
            ->assertJson([
                'name' => $request['name'],
            ]);

        $task = Task::find($response->decodeResponseJson('id'));
        $this->assertNotNull($task);
        $this->assertEquals($request['name'], $task->name);
        $this->assertNotEquals($request['name'], $originalName);
    }

    /**
     * @test
     */
    public function user_can_mark_tasks_as_completed()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list = $user->lists()->save(
            factory(TaskList::class)->make()
        );

        $task = $list->tasks()->save(
            factory(Task::class)->make()
        );

        $this->assertFalse($task->completed);

        $request = [
            'completed' => true,
        ];

        $route = route('v1.task', [$list->getKey(), $task->getKey()]);

        $this->patchJson($route, $request)
            ->assertStatus(200)
            ->assertJson([
                'completed' => true,
            ]);

        $after = $task->fresh();
        $this->assertTrue($after->completed);
    }

    /**
     * Make sure that the task is able to be deleted
     *
     * @test
     */
    public function delete_task_name()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list = factory(TaskList::class)->create(['user_id' => $user->id]);
        $task = factory(Task::class)->create(['list_id' => $list->id]);

        $this->deleteJson(route('v1.task', [$list->getKey(), $task->getKey()]))
            ->assertStatus(204);

        $this->assertNull($task->fresh());
    }

    /**
     * Make sure that user is able to retrieve tasks
     *
     * @test
     */
    public function user_can_get_tasks()
    {
        $user = factory(User::class)->create();

        $list = factory(TaskList::class)->make();
        $user->lists()->save($list);

        $list->tasks()->saveMany(
            factory(Task::class, 3)->make()
        );

        Sanctum::actingAs($user);
        $this->getJson(route('v1.list', [$list->getKey()]))
            ->assertStatus(200)
            ->assertJsonCount(3, 'tasks')
            ->assertJsonStructure([
                'tasks' => [
                    '*' => [
                        'id',
                        'name',
                        'completed',
                        'list_id',
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]);
    }
}
