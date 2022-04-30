<?php

namespace MOIREI\MediaLibrary\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ShareAuth
{
    public function handle(Request $request, Closure $next)
    {
        $shareable = $request->route('shared');
        if (Arr::get($shareable, 'public')) {
            return $next($request);
        }

        if (!empty(session("authenticated:$shareable->id"))) {
            $request->session()->put("authenticated:$shareable->id", time());
            return $next($request);
        }

        $name = config('media-library.route.name');
        $shared = $request->route('shared');

        return redirect()->route("${name}share.auth", ['shared' => $shared]);
    }
}
