<?php namespace Core;

class PublicCest {

    public function shouldGetSingleLanguage(FunctionalTester $I)
    {
        $I->sendGet(apiUrl('languages', ['pl']));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'code'       => 'pl',
                'i18n'       => 'pl_PL',
                'is_enabled' => true,
                'is_default' => false,
            ]
        );
    }

}
