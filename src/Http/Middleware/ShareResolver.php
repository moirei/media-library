<?php

namespace MOIREI\MediaLibrary\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Resolve shared file/folder relations
 * Cache or retrieve child paths
 */
class ShareResolver
{
    public function handle(Request $request, Closure $next)
    {
        // $path = $request->query('path', '/');
        // $request->request->add(['meta', 'value']);

        // Perform action
        return $next($request);
    }
}
