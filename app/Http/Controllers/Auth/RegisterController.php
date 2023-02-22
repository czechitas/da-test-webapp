<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Auth;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Models\User;
use CzechitasApp\Rules\EmailRule;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    /**
     *--------------------------------------------------------------------------
     * Register Controller
     *--------------------------------------------------------------------------
     *
     * This controller handles the registration of new users as well as their
     * validation and creation. By default this controller uses a trait to
     * provide this functionality without requiring any additional code.
     */
    use RegistersUsers;

    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Redirect after user is logged in
     */
    public function redirectTo(): string
    {
        return \route('students.index');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array<string, mixed> $data
     */
    protected function validator(array $data): ValidatorContract
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => ['required', new EmailRule(), 'unique:users'],
            'password' => ['required', Password::defaults(), 'confirmed'],
        ], [
            'email.unique' => \trans('auth.registration.validation.unique_email'),
            'password.confirmed' => \trans('auth.registration.validation.confirmed_pass'),
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array<string, mixed> $data
     */
    protected function create(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param         User $user
     * @return        mixed
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     */
    protected function registered(Request $request, $user)
    {
        return \redirect()->intended($this->redirectPath());
    }
}
