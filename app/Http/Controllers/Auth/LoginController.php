<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Auth;

use CzechitasApp\Http\Controllers\Controller;
use CzechitasApp\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     *--------------------------------------------------------------------------
     * Login Controller
     *--------------------------------------------------------------------------
     *
     * This controller handles authenticating users for the application and
     * redirecting them to your home screen. The controller uses a trait
     * to conveniently provide its functionality to your applications.
     */
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Redirect after user is logged in
     */
    public function redirectTo(): string
    {
        $user = Auth::user();
        if ($user->isRoleParent()) {
            return \route('students.index');
        }

        return \route('home');
    }

    /**
     * Show the application's login form.
     */
    public function showLoginForm(): View
    {
        $params = ['showLoginNotice' => false];

        $url = \session('url');
        if (!empty($url['intended']) && Str::contains($url['intended'], \route('students.create', [], false))) {
            $params['showLoginNotice'] = true;
        }

        return \view('auth.login', $params);
    }

    /**
     * The user has been authenticated.
     *
     * @param         User $user
     * @return        void
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    protected function authenticated(Request $request, $user)
    {
        return;
        // if ($user->is_blocked) {
        //     $this->guard()->logout();

        //     throw \Illuminate\Validation\ValidationException::withMessages([
        //         $this->username() => [\trans('auth.is_blocked')],
        //     ]);
        // }
    }
}
