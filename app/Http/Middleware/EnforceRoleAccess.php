<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceRoleAccess
{
    /** First URL segment => roles allowed to use that area. */
    private const AREAS = [
        'admin'      => ['administrative_admin'],
        'techadmin'  => ['technical_admin'],
        'technician' => ['technician'],
        'holder'               => ['administrative_admin', 'technical_admin', 'asset_holder', 'technician'],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // A deactivated account is signed out on its next request and cannot sign in again.
        if ($user && $user->is_active === false) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account has been deactivated. Contact your Technical Admin.',
            ]);
        }

        if ($user) {
            $area = $request->segment(1);

            // Acknowledging an assignment is open to any signed-in user (the controller checks ownership).
            $exempt = $request->is('holder/assignments/*');

            if (! $exempt && isset(self::AREAS[$area]) && ! in_array($user->role, self::AREAS[$area], true)) {
                abort(403, 'You do not have access to this area.');
            }
        }

        return $next($request);
    }
}
