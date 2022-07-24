<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Controllers\Auth;

use CzechitasApp\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

class VerificationController extends Controller
{
    /**
     *--------------------------------------------------------------------------
     * Email Verification Controller
     *--------------------------------------------------------------------------
     *
     * This controller is responsible for handling email verification for any
     * user that recently registered with the application. Emails may also
     * be resent if the user did not receive the original email message.
     */
    use VerifiesEmails;

    /**
     * Where to redirect users after verification.
     */
    protected string $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('signed')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }
}
