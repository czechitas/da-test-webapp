<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Auth;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Rules\EmailRule;
use CzechitasApp\Services\BreadcrumbService;
use Illuminate\Foundation\Auth\ResetsPasswords;
use PasswordRule\PasswordRule;

class ResetPasswordController extends Controller
{
    /**
     *--------------------------------------------------------------------------
     * Password Reset Controller
     *--------------------------------------------------------------------------
     *
     * This controller is responsible for handling password reset requests
     * and uses a simple trait to include this behavior. You're free to
     * explore this trait and override any methods you wish to tweak.
     */
    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     */
    protected string $redirectTo = '/';

    public function __construct(BreadcrumbService $breadcrumbService)
    {
        $this->middleware('guest');

        $breadcrumbService
            ->addLevel('login', \trans('auth.login.title'))
            ->addLevelWithUrl('', \trans('auth.forget.title'));
    }

    /**
     * Get the password reset validation error messages.
     *
     * @return        array<string, string>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    protected function validationErrorMessages()
    {
        return [
            'password.confirmed' => \trans('auth.registration.validation.confirmed_pass'),
        ];
    }

    /**
     * Get the password reset validation rules.
     *
     * @return        array<string, mixed>
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => ['required', new EmailRule()],
            'password' => ['required', new PasswordRule(), 'confirmed'],
        ];
    }
}
