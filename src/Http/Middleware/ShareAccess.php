<?php

namespace MOIREI\MediaLibrary\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ShareAccess
{
    public function handle(Request $request, Closure $next)
    {
        $shareable = $request->route('shared');

        if ($shareable) {
            if ($shareable->expired) {
                abort(400);
            }
            if ($user = $request->user()) {
                if (!$shareable->canAccess($user)) {
                    abort(401);
                }
            }
        }

        return $next($request);
    }
}
