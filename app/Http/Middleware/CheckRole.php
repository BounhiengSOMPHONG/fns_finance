<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage in routes: ->middleware('role:admin')
     *                  ->middleware('role:admin,accountant')  ← multiple roles allowed
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRoleName = Auth::user()?->role?->role_name;

        if (! in_array($userRoleName, $roles)) {
            abort(403, 'ທ່ານບໍ່ມີສິດໃນການເຂົ້າເຖິງໜ້ານີ້');
        }

        return $next($request);
    }
}
