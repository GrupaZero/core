<?php namespace Gzero\Core\Http\Controllers;

use Gzero\Core\Services\TimezoneService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller {

    /** @var TimezoneService */
    protected $timezones;

    /**
     * AccountController constructor.
     *
     * @param TimezoneService $timezones timezone service
     */
    public function __construct(TimezoneService $timezones)
    {
        $this->timezones = $timezones;
    }

    /**
     * Show account main page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('gzero-core::account.index');
    }

    /**
     * Edit account settings
     *
     * @param Request $request Request object
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        return view('gzero-core::account.edit', [
            'isUserEmailSet' => strpos($request->user()->email, '@'),
            'timezones'      => $this->timezones->getAvailableTimezones()
        ]);
    }

    /**
     * Show OAuth section
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function oauth()
    {
        return view('gzero-core::account.oauth');
    }

    /**
     * Show welcome page for registered user.
     *
     * @param  \Illuminate\Http\Request $request Request object
     *
     * @return mixed
     */
    public function welcome(Request $request)
    {
        if (session()->has('showWelcomePage')) {
            session()->forget('showWelcomePage');

            return view('gzero-core::account.welcome', ['method' => $request->get('method')]);
        }

        return redirect()->to(routeMl('home'));
    }
}
