<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProtectDemoAccount
{
    /**
     * @var list<array{method: string, path: string, name: string}>
     */
    private const PROTECTED_ROUTES = [
        ['method' => 'PUT', 'path' => 'user/profile-information', 'name' => 'user-profile-information.update'],
        ['method' => 'PUT', 'path' => 'user/password', 'name' => 'user-password.update'],
        ['method' => 'POST', 'path' => 'user/two-factor-authentication', 'name' => 'two-factor.enable'],
        ['method' => 'POST', 'path' => 'user/confirmed-two-factor-authentication', 'name' => 'two-factor.confirm'],
        ['method' => 'DELETE', 'path' => 'user/two-factor-authentication', 'name' => 'two-factor.disable'],
        ['method' => 'GET', 'path' => 'user/two-factor-qr-code', 'name' => 'two-factor.qr-code'],
        ['method' => 'GET', 'path' => 'user/two-factor-secret-key', 'name' => 'two-factor.secret-key'],
        ['method' => 'GET', 'path' => 'user/two-factor-recovery-codes', 'name' => 'two-factor.recovery-codes'],
        ['method' => 'POST', 'path' => 'user/two-factor-recovery-codes', 'name' => 'two-factor.regenerate-recovery-codes'],
        ['method' => 'DELETE', 'path' => 'user/other-browser-sessions', 'name' => 'other-browser-sessions.destroy'],
        ['method' => 'DELETE', 'path' => 'user/profile-photo', 'name' => 'current-user-photo.destroy'],
        ['method' => 'DELETE', 'path' => 'user', 'name' => 'current-user.destroy'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! config('demo.enabled')) {
            return $next($request);
        }

        $user = Auth::guard('web')->user();

        if (! $user || $user->email !== config('demo.user_email')) {
            return $next($request);
        }

        foreach (self::PROTECTED_ROUTES as $protectedRoute) {
            $matchesRoute = $request->route()?->getName() === $protectedRoute['name']
                || $request->path() === $protectedRoute['path'];

            if ($matchesRoute && $request->isMethod($protectedRoute['method'])) {
                abort(403, 'The public demo account cannot modify authentication settings.');
            }
        }

        return $next($request);
    }
}
