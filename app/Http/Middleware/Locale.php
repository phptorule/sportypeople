<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;

class Locale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ( ! empty(session('locale')) && array_key_exists(session('locale'), config('app.locales')))
        {
            App::setLocale(session('locale'));
        }
        else
        {
            App::setLocale(config('app.fallback_locale'));
        }
        return $next($request);
    }
}
