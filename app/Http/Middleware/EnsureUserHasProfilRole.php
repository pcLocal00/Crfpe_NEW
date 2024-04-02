<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasProfilRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $profil, $role)
    {
        $userProfiles=Auth::user()->roles->pluck('profil.code');
        $userRoles=Auth::user()->roles->pluck('code');
        //dd($userProfiles,$userRoles);
        if(!$userProfiles->contains($profil) || !$userRoles->contains($role)){
            return redirect('/');
        }
        return $next($request);
    }
}
