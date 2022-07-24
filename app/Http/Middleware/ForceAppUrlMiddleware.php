<?php

declare(strict_types=1);

namespace CzechitasApp\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ForceAppUrlMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (\config('app.force_url')) {
            $expectedHost = \preg_replace('#https?://([^/]*)/*#', '$1', \config('app.url'));
            $host = \trim($request->header('host'), '/');

            if ($expectedHost !== $host) {
                $url = $request->secure() ? 'https://' : 'http://';
                $url .= $expectedHost . '/' . \ltrim($request->getRequestUri(), '/');

                $code = \intval(\config('app.force_url_redirect_code')) === 301 ? 301 : 302;

                return Redirect::to($url, $code);
            }
        }

        return $next($request);
    }
}
