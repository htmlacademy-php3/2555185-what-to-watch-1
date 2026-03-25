<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
  /*
   * Handle an incoming request.
   *
   * @param  \Closure  $next
   * @param  mixed  ...$roles
   *
   */
  public function handle(Request $request, Closure $next, ...$roles): Response
  {
    // Проверяем, аутентифицирован ли пользователь
    if (!$request->user()) {
      return response()->json([
        'message' => 'Unauthenticated'
      ], 401);
    }

    // Проверяем, имеет ли пользователь нужную роль
    if (!in_array($request->user()->role, $roles)) {
      return response()->json([
        'message' => 'Access denied. Required roles: ' . implode(', ', $roles)
      ], 403);
    }

    return $next($request);
  }
}
