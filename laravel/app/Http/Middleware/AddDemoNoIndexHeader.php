<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddDemoNoIndexHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        return self::apply($next($request), $request);
    }

    public static function apply(Response $response, Request $request): Response
    {
        if (config('demo.enabled') && ! $request->is('api/*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
