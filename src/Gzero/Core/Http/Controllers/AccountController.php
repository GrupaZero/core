<?php namespace Gzero\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AccountController extends Controller {

    /**
     * Show account main page
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        return view('gzero-base::account.index');
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
        return view('gzero-base::account.edit', ['isUserEmailSet' => strpos($request->user()->email, '@')]);
    }

    /**
     * Show OAuth section
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function oauth()
    {
        return view('gzero-base::account.oauth');
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

            return view('gzero-base::account.welcome', ['method' => $request->get('method')]);
        }

        return redirect()->to(routeMl('home'));
    }
}
