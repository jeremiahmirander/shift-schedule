<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class AuthController extends Controller
{
    use ThrottlesLogins;

    public function __construct()
    {
        $this->middleware(['guest'])->only(['forgot', 'reset']);
        $this->middleware(['auth:sanctum'])->only(['logout']);
    }

    /**
     * Handle a registration request for the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    {
        if ($request->user()) {
            throw new ConflictHttpException("You're already logged in");
        }

        $user = User::create([
            'email'    => $request->input('email'),
            'password' => Hash::make($request->input('password')),
        ]);

        event(new Registered($user));

        // Stateful
        if ($request->attributes->get('sanctum')) {
            $this->guard()->login($user);

            return response($user, 201);
        }

        // Stateless
        $new_token = $user->createToken($request->input('device_name'));

        return response([
            'user'    => $user,
            'token'   => $new_token->plainTextToken,
            'details' => $new_token->accessToken->toArray(),
        ], 201);
    }

    /**
     * Handle a login request to the application.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(LoginRequest $request)
    {
        if ($request->user()) {
            throw new ConflictHttpException("You're already logged in");
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($request->attributes->get('sanctum')
            && $this->attemptStatefulLogin($request)) {
            // Stateful
            $request->session()->regenerate();

            $this->clearLoginAttempts($request);

            return $this->guard()->user();
        } elseif ($this->attemptStatelessLogin($request)) {
            // Stateless
            /** @var \App\User */
            $user = $this->guard()->user();

            $new_token = $user->createToken(
                $request->input('device_name')
            );

            return [
                'user'    => $user,
                'token'   => $new_token->plainTextToken,
                'details' => $new_token->accessToken->toArray(),
            ];
        }

        $this->incrementLoginAttempts($request);

        throw ValidationException::withMessages([$this->username() => [trans('auth.failed')]]);
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        if ($request->attributes->get('sanctum')) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } else {
            // must be using an api token
            $request->user()->currentAccessToken()->delete();
        }

        return response(null, 204);
    }

    /**
     * Send a reset link to the given user.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function forgot(Request $request)
    {
        $request->validate([
            $this->username() => ['required'],
        ]);

        $response = $this->broker()->sendResetLink(
            $request->only([$this->username()])
        );

        if (Password::RESET_LINK_SENT === $response) {
            return response(['message' => trans($response)], 200);
        }

        throw ValidationException::withMessages([$this->username() => [trans($response)]]);
    }

    /**
     * Reset the given user's password.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'           => ['required'],
            $this->username() => ['required'],
            'password'        => ['required', 'confirmed', 'min:8'],
        ]);

        $response = $this->broker()->reset(
            $request->only('token', $this->username(), 'password'),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        if (Password::PASSWORD_RESET === $response) {
            return response(['message' => trans($response)], 200);
        }

        throw ValidationException::withMessages([$this->username() => [trans($response)]]);
    }

    /**
     * @return bool
     */
    protected function attemptStatefulLogin(Request $request)
    {
        return $this->guard()->attempt(
            $request->only($this->username(), 'password'),
            $request->filled('remember')
        );
    }

    /**
     * @return bool
     */
    protected function attemptStatelessLogin(Request $request)
    {
        $user = User::where('email', $request->input($this->username()))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return false;
        }

        $this->guard()->setUser($user);

        return true;
    }

    /**
     * Reset the given user's password.
     *
     * @param string $password
     *
     * @return void
     */
    protected function resetPassword(User $user, $password)
    {
        $user->password = Hash::make($password);

        $user->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));

        $this->guard()->login($user);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard|\Illuminate\Contracts\Auth\Guard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * @return string
     */
    protected function username()
    {
        return 'email';
    }

    /**
     * Get the broker to be used during password reset.
     *
     * @return \Illuminate\Contracts\Auth\PasswordBroker
     */
    public function broker()
    {
        return Password::broker();
    }
}
