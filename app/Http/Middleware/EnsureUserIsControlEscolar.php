<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsControlEscolar
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isControlEscolar()) {
            abort(403, 'Acceso restringido al personal de control escolar.');
        }

        return $next($request);
    }
}
