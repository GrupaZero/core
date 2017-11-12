<?php namespace Core;

class SecurityCest {

    public function shouldNotUpdateOptionValue(FunctionalTester $I)
    {
        $I->sendPUT(apiUrl('options/seo'),
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

        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

    public function shouldNotUpdateUsers(FunctionalTester $I)
    {
        $I->sendPATCH(apiUrl('users', [1]), ['password' => 'test']);

        $I->seeResponseCodeIs(401);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Unauthenticated.']);
    }

}
