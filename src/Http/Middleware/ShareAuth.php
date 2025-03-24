<?php

namespace MOIREI\MediaLibrary\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use MOIREI\MediaLibrary\Api;

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

        $shared = $request->route('shared');
        $url = Api::route("share.auth", ['shared' => $shared]);

        return redirect()->to($url);
    }
}
