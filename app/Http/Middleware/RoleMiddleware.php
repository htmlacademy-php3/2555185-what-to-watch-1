<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
  /*
   * Handle an incoming request.
   * @param  \Closure  $next
   * @param  mixed  ...$roles
   * @return mixed
   */
  public function handle(Request $request, Closure $next, ...$roles)
  {
    if (!auth()->check()) {
      return response()->json([
        'message' => 'Не авторизован'
      ], 401);
    }

    if (!auth()->user()->hasAnyRole($roles)) {
      return response()->json([
        'message' => 'Доступ запрещен. Недостаточно прав.'
      ], 403);
    }

    return $next($request);
  }
}
