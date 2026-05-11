<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Se o utilizador estiver logado e for administrador, pode passar
        if (Auth::check() && Auth::user()->is_admin) {
            return $next($request);
        }

        // Se for um cliente normal a tentar espreitar, manda-o de volta para o dashboard dele com um erro
        return redirect()->route('dashboard')->with('error', 'Acesso negado. Área restrita à Administração.');
    }
}