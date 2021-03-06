<?php namespace Gzero\Core\Http\Controllers\Auth;

use Gzero\Core\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @return string
     */
    protected function redirectTo()
    {
        return routeMl('home', $this->getEffectiveLocale());
    }

    /**
     * Returns user's set language or otherwise the application default language
     *
     * @return string
     */
    protected function getEffectiveLocale()
    {
        return $this->guard()->user()->language_code ?: app()->getLocale();
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('gzero-core::auth.login');
    }
}
