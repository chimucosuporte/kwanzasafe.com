<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que o utilizador autenticado (token Sanctum) é staff (is_admin).
 * Devolve JSON 403 — para a app admin, não redireciona como o IsAdmin web.
 */
class EnsureApiAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_admin) {
            return response()->json(['message' => 'Acesso restrito a administradores.'], 403);
        }

        return $next($request);
    }
}
