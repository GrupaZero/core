<?php namespace Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;

class BunnyController extends Controller {

    public function save(Request $request)
    {
        $request->validate(['bunny_date' => "iso8601"]);
        return "Bunny is happy";
    }
}

class Iso8601ValidatorCest {

    public function _before(FunctionalTester $I)
    {
        Route::group([
            'domain'     => 'api.' . config('gzero.domain'),
            'prefix'     => 'v1',
        ], function ($router) {
            $router->post('/save-bunny', "Core\BunnyController@save");
        });
    }

    public function canValidateValidCaseAgainstIso8601(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('save-bunny'), ['bunny_date' => '2019-07-04T12:32:12-0300']);

        $I->seeResponseCodeIs(200);
        $I->seeResponseContains("Bunny is happy");
    }

    public function canValidateInvalidCaseAgainstIso8601(FunctionalTester $I)
    {
        $I->sendPOST(apiUrl('save-bunny'), ['bunny_date' => '2019-07-04T12:32:12']);

        $I->seeResponseCodeIs(422);
    }
}
