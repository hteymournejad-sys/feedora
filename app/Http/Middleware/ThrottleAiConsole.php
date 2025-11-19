<?php

namespace App\Http\Middleware;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Closure;

class ThrottleAiConsole
{
public function handle($request, Closure $next)
{
RateLimiter::for('ai-console', function () {
return [Limit::perMinute(30)->by(request()->ip())];
});
return $next($request);
}
}