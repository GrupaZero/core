<?php namespace Core\api;

use Core\FunctionalTester;

class CORSCest {

    public function allowedHeadersOPTIONS(FunctionalTester $I)
    {
        $I->haveHttpHeader('Access-Control-Request-Headers', 'accept, x-requested-with');
        $I->haveHttpHeader('Access-Control-Request-Method', 'GET');
        $I->haveHttpHeader('Origin', 'http://example.com');

        $I->sendOPTIONS(apiUrl('languages'));

        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Access-Control-Allow-Credentials', 'true');
        $I->seeHttpHeader('Access-Control-Allow-Origin', 'http://example.com');
    }

    public function optionsPUTSuccessResponse(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Origin', 'https://example.com');

        $I->sendPUT(
            apiUrl('options/seo'),
            [
                'key'   => 'desc_length',
                'value' => [
                    'en' => 160,
                    'pl' => 161,
                    'de' => 162,
                    'fr' => 163,
                ],
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeHttpHeader('Access-Control-Allow-Credentials', 'true');
        $I->seeHttpHeader('Access-Control-Allow-Origin', 'https://example.com');
        $I->seeResponseIsJson();
    }

    public function validationError(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Origin', 'http://dev.gzero.pl');

        $I->sendPUT(apiUrl('options/seo'));

        $I->seeResponseCodeIs(422);
        $I->seeHttpHeader('Access-Control-Allow-Credentials', 'true');
        $I->seeHttpHeader('Access-Control-Allow-Origin', 'http://dev.gzero.pl');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'key'   => [0 => 'The key field is required.',],
                    'value' => [0 => 'The value field is required.',]
                ],
            ]
        );
    }

    public function validationErrorInDifferentLanguage(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveHttpHeader('Accept-Language', 'pl');
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Origin', 'http://dev.gzero.pl');

        // Adding PL translation on fly, because this file is stored in platform
        $I->haveApplicationHandler(function ($app) {
            $app->make('translator')->addLines(['validation.required' => 'Pole :attribute jest wymagane.'], 'pl');
        });

        $I->sendPUT(apiUrl('options/seo'));

        $I->seeResponseCodeIs(422);
        $I->seeHttpHeader('Access-Control-Allow-Credentials', 'true');
        $I->seeHttpHeader('Access-Control-Allow-Origin', 'http://dev.gzero.pl');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'key'   => [0 => 'Pole key jest wymagane.',],
                    'value' => [0 => 'Pole value jest wymagane.',]
                ],
            ]
        );
    }

    public function methodNotAllowedHttpException(FunctionalTester $I)
    {
        $I->haveHttpHeader('X-Requested-With', 'XMLHttpRequest');
        $I->haveHttpHeader('Origin', 'http://localhost');

        $I->sendPOST(apiUrl('options'));
        $I->seeResponseCodeIs(405);
        // Asserting CORS headers won't be added
        $I->dontSeeHttpHeader('Access-Control-Allow-Credentials', 'true');
        $I->dontSeeHttpHeader('Access-Control-Allow-Origin', 'http://localhost');
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'message' => 'Method not allowed',
            ]
        );
    }
}
