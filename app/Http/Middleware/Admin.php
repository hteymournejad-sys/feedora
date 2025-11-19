<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check() || Auth::user()->is_admin != 1) {
            return redirect()->route('home')->with('error', 'شما اجازه دسترسی به این بخش را ندارید.');
        }

        return $next($request);
    }
}