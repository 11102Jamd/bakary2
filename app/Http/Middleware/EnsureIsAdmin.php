<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $logData = [
            'user_id' => $user ? $user->id : 'null',
            'email' => $user ? $user->email : 'null',
            'rol' => $user ? $user->rol : 'null',
            'path' => $request->path(),
            'method' => $request->method(),
            'isAdmin' => $user ? $user->isAdmin() : false,
            'isBaker' => $user ? $user->isBaker() : false,
            'isCashier' => $user ? $user->isCashier() : false
        ];

        // Registro detallado d la verficacion
        Log::debug('EnsureIsAdmin Middleware Check', $logData);

        // Verificar autenticacion
        if (!$user) {
            Log::warning('Intento de acceso no autenticado a ruta de admin', [
                'ip' => $request->ip(),
                'path' => $request->path()
            ]);
            abort(401, 'No autenticado');
        }

        //verificar rol de administrador
        if (!$user || !$user->isAdmin()) {
            abort($user ? 403 : 401, $user ? 'Acceso solo para administradores' : 'No autenticado');
        }

        return $next($request);
    }
}
