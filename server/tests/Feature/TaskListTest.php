<?php

namespace Tests\Feature;

use App\User;
use App\TaskList;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class TaskListTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function user_can_create_lists()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $request = [
            'name' => $this->faker->words(rand(2, 3), true)
        ];

        $response = $this->postJson(route('v1.lists'), $request)
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'name',
            ])
            ->assertJson([
                'name' => $request['name'],
            ]);

        $list = TaskList::find($response->decodeResponseJson('id'));
        $this->assertNotNull($list);
        $this->assertEquals($request['name'], $list->name);
    }

    /**
     * @test
     */
    public function validates_create_lists_request()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $request = [
            'newName' => $this->faker->words(rand(2, 3), true)
        ];

        $this->postJson(route('v1.lists'), $request)
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function users_can_get_lists()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $request = [
            'user' => $user
        ];

        $this->getJson(route('v1.lists', $request))
            ->assertStatus(200)
            ->assertJsonStructure([ '*' => [
                'id',
                'created_at',
                'updated_at',
                'name',
                'user_id',
            ]]);
    }

    /**
     * @test
     */
    public function users_can_edit_lists()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list = factory(TaskList::class)->create([
            'user_id' => $user->id,
        ]);

        $request = [
            'name' => $this->faker->words(rand(2, 3), true)
        ];

        $this->patchJson(route('v1.list', $list), $request)
            ->assertStatus(200);

        $after = $list->fresh();
        $this->assertEquals($request['name'], $after->name, "Name not changed");
    }

    /**
     * @test
     */
    public function validates_edit_lists_request()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list = factory(TaskList::class)->create(['user_id' => $user->id]);

        $request = [
            'listName' => $this->faker->words(rand(2, 3), true)
        ];

        $this->patchJson(route('v1.list', $list), $request)
            ->assertStatus(422);
    }

    /**
     * @test
     */
    public function users_can_delete_lists()
    {
        $user = factory(User::class)->create();
        Sanctum::actingAs($user);

        $list = factory(TaskList::class)->create([
            'user_id' => $user->id
        ]);

        $this->deleteJson(route('v1.list', $list))
            ->assertStatus(204);

        $this->assertNull($list->fresh());
    }
}
