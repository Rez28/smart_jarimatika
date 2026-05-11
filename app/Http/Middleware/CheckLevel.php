<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckLevel
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah user sudah login dan level < 5
        if (Auth::check() && Auth::user()->level < 5) {
            return redirect()->route('dashboard')->with('error', 'Kamu harus mencapai Level 5 untuk membuka mode ini!');
        }

        return $next($request);
    }
}
