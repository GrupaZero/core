<?php namespace Gzero\Core\Http\Controllers\Auth;

use Gzero\Core\Http\Controllers\Controller;
use Gzero\Core\Jobs\CreateUser;
use Gzero\Core\Jobs\SendWelcomeEmail;
use Gzero\Core\Services\TimezoneService;
use Gzero\Core\Services\UserService;
use Gzero\Core\Validators\BaseUserValidator;
use Gzero\Core\Validators\UserValidator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller {
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    | This is our implementation of native Illuminate\Foundation\Auth\RegistersUsers;
    |
    */

    use RedirectsUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @return string
     */
    protected function getEffectiveLocale()
    {
        return $this->guard()->user()->language_code ?: app()->getLocale();
    }

    /** @var UserValidator */
    protected $validator;

    /** @var TimezoneService */
    protected $timezones;

    /**
     * Create a new controller instance.
     *
     * @param UserValidator   $validator Validator
     * @param TimezoneService $timezones timezone service
     */
    public function __construct(UserValidator $validator, TimezoneService $timezones)
    {
        $this->validator = $validator;
        $this->timezones = $timezones;
        $this->middleware('guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        return view('gzero-core::auth.register', ['timezones' => $this->timezones->getAvailableTimezones()]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request Request
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // Preventing spamer registration
        if ($request->input('account_intent')) {
            return redirect()->route('home');
        }

        $this->validator->setData($request->all());
        $input = $this->validator->validate('register');
        $user  = dispatch_now(new CreateUser($input));
        event(new Registered($user));

        $this->guard()->login($user);

        dispatch(new SendWelcomeEmail($user));
        session()->put('showWelcomePage', true);

        return redirect(routeMl('account.welcome', $this->getEffectiveLocale()));
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
}
