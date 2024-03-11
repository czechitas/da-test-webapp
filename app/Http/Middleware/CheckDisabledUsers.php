<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Prologue\Alerts\Facades\Alert;

class CheckDisabledUsers
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // $user = Auth::user();

        // if ($user && $user->is_blocked) {
        //     Auth::logout();
        //     Alert::error(\trans('auth.is_blocked'))->flash();

        //     return \redirect()->route('login');
        // }

        return $next($request);
    }
}
