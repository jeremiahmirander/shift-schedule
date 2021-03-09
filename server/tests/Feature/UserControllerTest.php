<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function returns_profile()
    {
        $user = factory(User::class)->create();

        Sanctum::actingAs($user);
        $this->getJson(route('v1.profile'))
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'email',
            ])
            ->assertJson([
                'id' => $user->getKey(),
            ]);
    }

    /**
     * @test
     */
    public function must_be_logged_in_to_see_profile()
    {
        $this->getJson(route('v1.profile'))
            ->assertStatus(401);
    }

    /**
     * @test
     */
    public function updates_profile()
    {
        $user = factory(User::class)->create();

        $before_email    = $user->email;
        $before_password = $user->password;

        $request = [
            'email'    => $this->faker->safeEmail,
            'password' => Str::random(12),
        ];

        Sanctum::actingAs($user);
        $this->patchJson(route('v1.profile'), $request)
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'email',
            ])
            ->assertJson([
                'email' => $request['email'],
            ]);

        $after = $user->fresh();
        $this->assertEquals($request['email'], $after->email);
        $this->assertNotEquals($before_email, $after->email);
        $this->assertNotEquals($before_password, $after->password);
    }

    /**
     * @test
     */
    public function validates_update_profile_requests()
    {
        $before = factory(User::class)->create();
        $user   = factory(User::class)->create();

        $request = [
            'email'    => $before->email,
            'password' => '🤪',
        ];

        Sanctum::actingAs($user);
        $this->patchJson(route('v1.profile'), $request)
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                    'password',
                ],
            ]);
    }
}
