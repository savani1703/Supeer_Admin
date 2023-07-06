<?php

namespace App\Http\Middleware;

use App\Exceptions\UnauthorizedRequest;
use Closure;
use Illuminate\Http\Request;

class AccessControl
{
    /**
     * @throws UnauthorizedRequest
     */
    public function handle(Request $request, Closure $next)
    {
        if(strcmp($request->getRealMethod(), "POST") === 0) {
            return $next($request);
        }
        $route = $request->route()->compiled->getStaticPrefix();
        if((new \App\Plugin\AccessControl\AccessControl())->hasAccessRoute($route)) {
            return $next($request);
        }
        throw new UnauthorizedRequest();
    }
}
