<?php namespace Core;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class BunnyController extends Controller {

    public function save(Request $request)
    {
        $request->validate(['bunny_date' => "iso8601"]);
    }
}

class Iso8601ValidatorCest {

    public function _before(FunctionalTester $I)
    {
        Route::post('/save-bunny', "Core\BunnyController@save");
    }

    public function canValidateValidCaseAgainstIso8601(FunctionalTester $I)
    {
        $I->sendPOST('/save-bunny', ['bunny_date' => '2019-07-04T12:32:12-0300']);

        $I->seeResponseCodeIs(200);
    }

    public function canValidateInvalidCaseAgainstIso8601(FunctionalTester $I)
    {
        $I->sendPOST('/save-bunny', ['bunny_date' => '2019-07-04T12:32:12']);

        $I->seeResponseCodeIs(404);
    }
}
