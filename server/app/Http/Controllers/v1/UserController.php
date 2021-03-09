<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function me(Request $request)
    {
        return $request->user();
    }

    public function update(UserRequest $request)
    {
        /** @var \App\User */
        $user = $request->user();

        $user->fill($request->except(['password']));

        if ($request->has('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        return $user;
    }
}
