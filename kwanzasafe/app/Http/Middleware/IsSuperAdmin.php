<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe o acesso ao "admin máximo" (super-admin).
 *
 * Usado em áreas exclusivas: gestão de funcionários, arbitragem de recursos,
 * relatórios de falha completos.
 */
class IsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->isSuperAdmin()) {
            return $next($request);
        }

        // Staff de suporte volta ao painel admin; clientes ao dashboard.
        if (Auth::check() && Auth::user()->isStaff()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Acesso restrito ao Super-Administrador.');
        }

        return redirect()->route('dashboard')
            ->with('error', 'Acesso negado. Área restrita à Administração.');
    }
}
