<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Api\Auth;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Http\Requests\Api\Profile\RegisterUserRequest;
use CzechitasApp\Models\Enums\UserRole;
use CzechitasApp\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends Controller
{
    public function register(RegisterUserRequest $request): Response
    {
        $user = new User([
            'name'      => $request->input('name'),
            'email'     => $request->input('email'),
            'password'  => Hash::make($request->input('password')),
            'role'      => UserRole::PARENT,
        ]);
        $user->save();
        $user->access_token = $user->createToken('api')->plainTextToken;
        $user->save();

        return \response()->json(
            \array_merge($user->toArray(), ['access_token' => $user->access_token]),
            Response::HTTP_CREATED,
        );
    }

    public function current(Request $request): Response
    {
        return \response()->json($request->user());
    }
}
