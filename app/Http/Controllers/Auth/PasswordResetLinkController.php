<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.forgot-password');
    }

    public function getnewpass()
    {
        return view('auth.changemdp');
    }

    public function updatepassword()
    {
        return view('auth.updatemdp');
    }

    public function getnewmdp()
    {
        return view('auth.getnewmdp');
    }
    
    

    // public function updatenewpass(Request $request)
    // {
    //     // $token = $request->bearerToken();
    //     dd(1);
    //     $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required|string|confirmed|min:8',
    //     ]);

    //     // Here we will attempt to reset the user's password. If it is successful we
    //     // will update the password on an actual user model and persist it to the
    //     // database. Otherwise we will parse the error and return the response.
    //     $status = Password::reset(
    //         $request->only('email', 'password', 'password_confirmation'),
    //         function ($user) use ($request) {
    //             $user->forceFill([
    //                 'password' => Hash::make($request->password),
    //             ])->save();

    //             event(new PasswordReset($user));
    //         }
    //     );

    //     // If the password was successfully reset, we will redirect the user back to
    //     // the application's home authenticated view. If there is an error we can
    //     // redirect them back to where they came from with their error message.
    //     return $status == Password::PASSWORD_RESET
    //                 ? redirect()->route('login')->with('status', __($status))
    //                 : back()->withInput($request->only('email'))
    //                         ->withErrors(['email' => __($status)]);
    // }


        /**
     * changement of password
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatenewpass(Request $request)
    {     
        $msg = "verifier vos cordonnees";
        $success= false;

        if (Auth::validate(['email' => $request->email, 'password' => $request->oldpass]))
        {
            $this->validate($request, [
                'newpass1' => 'required_with:address:newpass2|same:newpass2',
            ]);

            $user = User::where('email', 'like', $request->email)->first();
            $user->password = Hash::make($request->newpass);
            $user->save();
         
            $success=true;
            $msg = 'Mot de passe modifie avec succes.';

        }
        
        return (['success'=>$success,'message'=>$msg]);
       
    }
    /**
     * Handle an incoming password reset link request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
    }

    public function resetpassword(Request $request)
    {     
        // $host=env('HOST');
        $host = config('app.host');
        $msg = "verifier vos cordonnees";
        $success= false;

        $user = User::where('email', 'like', $request->email)->first();
        if ($user) { 
            try{
                /* Mail sending */
                $fullname = ucfirst($user->name ?? '') . ' ' . ucfirst($user->lastname ?? '');
                $content = "Bonjour $fullname,<br/><br/>Vous avez demandé à récupérer vos <b>codes d'accès</b><br/>Cette opération vous attribuera un nouveau <b>mot de passe</b><br/>";
                $content .= "Si vous souhaitez confirmer cette demande, cliquer sur le lien suivant : <a href='$host/getnewmdp'>récupérer votre mot de passe</a>.<br/><br/>";
                $content .= "Nous sommes à votre disposition pour toute information complémentaire.<br/><br/>";
                $header = "Environnement de formation pour CRFPE";
                $footer = "Plateforme de formation SOLARIS";

                Mail::send('pages.email.model', ['htmlMain' => $content, 'htmlHeader' => $header, 'htmlFooter' => $footer], function ($m) use ($request, $fullname) {
                    $m->from(auth()->user()->email);
                    $m->bcc([auth()->user()->email,'hbriere@havetdigital.fr']);
                    $m->to($request->email, $fullname)->subject('Récupération de votre compte sur CRFPE');
                });

                $success=true;
                $msg = 'Merci de consulter votre boite mail pour réinitialiser le mot de passe.';
            } catch (Exception $e) {
                $success = false;
                $msg = $request->email . ': Erreur Inconnue.';
            }
        } else {
            $msg = 'Cette adresse mail ' . $request->email . ' est n\'est jamais utilisé pour un compte';
            $success= false;
        }
        
        return (['success'=>$success,'message'=>$msg]);
       
    }

    public function generatepass(Request $request)
    {     
        $msg = "verifier vos cordonnees";
        $success= false;
        $user = User::where('email', 'like', $request->email)->first();
        if ($user) { 
            try{
                $user->password = Hash::make($request->newpass);
                $user->save();

                $success=true;
                $msg = 'Votre mot de pass a été bien réinitialisé';
            } catch (Exception $e) {
                $success = false;
                $msg = $request->email . ': Erreur Inconnue.';
            }
        } else {
            $msg = 'Cette adresse mail ' . $request->email . ' est n\'est jamais utilisé pour un compte';
            $success= false;
        }
        
        return (['success'=>$success,'message'=>$msg]);
       
    }
}
