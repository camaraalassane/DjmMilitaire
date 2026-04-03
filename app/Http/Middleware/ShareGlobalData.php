<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Alerte;
use Illuminate\Support\Facades\Auth; // AJOUTEZ CETTE LIGNE

class ShareGlobalData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) { // Utilisez Auth::check() au lieu de auth()->check()
            Inertia::share([
                'alertesCount' => Alerte::where('est_vue', false)->count(),
            ]);
        }

        return $next($request);
    }
}