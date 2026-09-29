<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Menendang user berstatus suspended keluar dari semua area auth:
 * logout paksa + redirect ke login (atau 403 JSON untuk request API/JSON).
 */
class CheckSuspended
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ($user->status ?? 'active') === 'suspended') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akun Anda di-suspend.'], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'Akun Anda di-suspend. Hubungi admin.',
            ]);
        }

        return $next($request);
    }
}
