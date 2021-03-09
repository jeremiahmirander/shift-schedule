<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**
     * @test
     */
    public function registers_users_from_web_app()
    {
        $password = Str::random(12);

        $request = [
            'email'    => $this->faker->safeEmail,
            'password' => $password,
        ];

        $this->postJson(route('v1.register'), $request, ['referer' => config('app.url')])
            ->assertStatus(201)
            ->assertJsonStructure([
                'id',
                'email',
            ]);

        $user = User::where('email', $request['email'])->first();
        $this->assertNotNull($user);
        $this->assertEquals($user->getAuthIdentifier(), Auth::user()->getAuthIdentifier());
    }

    /**
     * @test
     */
    public function registers_users_from_other_apps()
    {
        $password = Str::random(12);

        $request = [
            'device_name' => 'ios_mobile',
            'email'       => $this->faker->safeEmail,
            'password'    => $password,
        ];

        $this->postJson(route('v1.register'), $request, ['referer' => 'https://www.somwhere-else.com/'])
            ->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                ],
                'token',
                'details' => [],
            ]);

        $user = User::where('email', $request['email'])->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->tokens()->first());
    }

    /**
     * @test
     */
    public function users_cant_register_while_logged_in()
    {
        $password = Str::random(12);

        $request = [
            'email'    => $this->faker->safeEmail,
            'password' => $password,
        ];

        Sanctum::actingAs(factory(User::class)->create());

        $this->postJson(route('v1.register'), $request, ['referer' => config('app.url')])
            ->assertStatus(409)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * Stateful authentication uses the referer and a session cookie.
     *
     * @test
     */
    public function logs_users_in_to_web_app()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        $this->postJson(route('v1.login'), [
            'email'    => $user->email,
            'password' => $password,
            'remember' => 1,
        ], ['referer' => config('app.url')])
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'email',
            ]);

        $this->assertEquals($user->getAuthIdentifier(), Auth::user()->getAuthIdentifier());
    }

    /**
     * Stateless authentication uses a bearer token.
     *
     * @test
     */
    public function logs_users_in_from_other_apps()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        $this->postJson(route('v1.login'), [
            'device_name' => 'ios_mobile',
            'email'       => $user->email,
            'password'    => $password,
        ], ['referer' => 'https://www.somewhere-else.com/'])
            ->assertStatus(200)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'email',
                ],
                'token',
                'details' => [],
            ]);

        $this->assertNotNull($user->tokens()->first());
    }

    /**
     * @test
     */
    public function users_cant_log_in_twice()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        Sanctum::actingAs($user);

        $this->postJson(route('v1.login'), [
            'email'    => $user->email,
            'password' => $password,
            'remember' => 1,
        ], ['referer' => config('app.url')])
            ->assertStatus(409)
            ->assertJsonStructure([
                'message',
            ]);
    }

    /**
     * @test
     */
    public function validates_web_app_login_requests()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        $this->postJson(route('v1.login'), [
            'email'    => $user->email,
            'password' => $password.'😎',
            'remember' => 1,
        ], ['referer' => config('app.url')])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);

        $this->assertNull(Auth::user());
    }

    /**
     * @test
     */
    public function validates_other_app_login_requests()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        $this->postJson(route('v1.login'), [
            'device_name' => 'ios_mobile',
            'email'       => $user->email,
            'password'    => $password.'😎',
        ], ['referer' => 'https://www.somewhere-else.com/'])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);

        $this->assertNull(Auth::user());
    }

    /**
     * @test
     */
    public function throttles_login_requests()
    {
        $password = Str::random(12);

        $user = factory(User::class)->create([
            'password' => Hash::make($password),
        ]);

        // fake the user having tried to log in a bunch of times
        $throttle_key = Str::lower($user->email.'|'.'127.0.0.1');
        $cache        = app(\Illuminate\Contracts\Cache\Repository::class);
        $cache->put($throttle_key, 999, 60);
        $cache->put($throttle_key.':timer', now()->addRealSeconds(60)->getTimestamp(), 60);

        Event::fake();

        $this->postJson(route('v1.login'), [
            'email'    => $user->email,
            'password' => $password,
        ], ['referer' => config('app.url')])
            ->assertStatus(429)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);

        Event::assertDispatched(Lockout::class);

        $this->assertNull(Auth::user());
    }

    /**
     * Web app uses a stateful guard via the referer and cookies to
     * determine if the user is logged in.
     *
     * @test
     */
    public function logs_users_out_of_web_app()
    {
        $user = factory(User::class)->create();

        Sanctum::actingAs($user);

        $this->postJson(
            route('v1.logout'),
            [],
            ['referer' => config('app.url')]
        )
            ->assertStatus(204);
    }

    /**
     * @test
     */
    public function user_must_be_logged_in_to_log_out()
    {
        $this->postJson(route('v1.logout'))
            ->assertStatus(401);
    }

    /**
     * Other applications use a stateless bearer token to
     * determine if the user is logged in.
     *
     * @test
     */
    public function logs_users_out_of_other_apps()
    {
        $user  = factory(User::class)->create();
        $token = $user->createToken('token');

        $this->postJson(
            route('v1.logout'),
            [],
            ['Authorization' => "Bearer {$token->plainTextToken}"]
        )
            ->assertStatus(204);

        $this->assertNull($token->accessToken->fresh());
    }

    /**
     * @test
     */
    public function users_can_request_password_resets()
    {
        $user = factory(User::class)->create();

        Notification::fake();

        $this->postJson(route('v1.forgot'), [
            'email' => $user->email,
        ], ['referer' => config('app.url')])
            ->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /**
     * @test
     */
    public function validates_password_reset_requests()
    {
        $user = factory(User::class)->create();

        Notification::fake();

        $this->postJson(route('v1.forgot'), [
            'email' => $user->email.'😎',
        ], ['referer' => config('app.url')])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);

        Notification::assertNotSentTo($user, ResetPassword::class);
    }

    /**
     * @test
     */
    public function users_can_reset_passwords()
    {
        $user     = factory(User::class)->create();
        $token    = app(PasswordBroker::class)->createToken($user);
        $password = Str::random(12);

        Event::fake();

        $this->postJson(route('v1.reset'), [
            'token'                 => $token,
            'email'                 => $user->email,
            'password'              => $password,
            'password_confirmation' => $password,
        ])
            ->assertStatus(200);

        Event::assertDispatched(PasswordReset::class);
        $this->assertNotEquals($user->fresh()->password, $user->password);
    }

    /**
     * @test
     */
    public function validates_reset_password_requests()
    {
        $user     = factory(User::class)->create();
        $token    = app(PasswordBroker::class)->createToken($user);
        $password = Str::random(12);

        Event::fake();

        $this->postJson(route('v1.reset'), [
            'token'                 => $token.'😎',
            'email'                 => $user->email,
            'password'              => $password,
            'password_confirmation' => $password,
        ])
            ->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    'email',
                ],
            ]);

        Event::assertNotDispatched(PasswordReset::class);
        $this->assertEquals($user->fresh()->password, $user->password);
    }
}
