<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{

    public function authenticateVerify(Request $request)
    {
        $success=false;
        $msg = 'Les informations fournies ne sont pas valides.';
        $email = $request->email;
        $password = $request->password;
        $remember = false;
        if ($request->has('remember')) {
            $remember =$request->remember;
        }
        if (Auth::attempt(['email' => $email, 'password' => $password, 'active' => 1],$remember)) {
            $request->session()->regenerate();
            $success=true;
            $msg = 'ok';
        }
        return response()->json([
            'success' => $success,
            'msg' => $msg,
        ]);
    }

    /**
     * Display the login view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /* public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect(RouteServiceProvider::HOME);
    } */

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
