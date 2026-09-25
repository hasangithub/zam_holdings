<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HeadOfficeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if ((int) auth()->user()->branch_id !== 3) {
            abort(403, 'Only Head Office users can access this section.');
        }

        return $next($request);
    }
}