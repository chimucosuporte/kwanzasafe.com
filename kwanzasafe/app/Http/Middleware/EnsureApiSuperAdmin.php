<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Garante que o utilizador autenticado (token Sanctum) é super-admin.
 * Devolve JSON 403 — para endpoints exclusivos do super-admin na app admin.
 */
class EnsureApiSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isSuperAdmin()) {
            return response()->json(['message' => 'Acesso restrito a super-administradores.'], 403);
        }

        return $next($request);
    }
}
