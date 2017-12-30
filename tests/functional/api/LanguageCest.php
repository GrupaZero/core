<?php namespace Core\api;

use Core\FunctionalTester;

class LanguageCest {

    public function shouldGetSingleLanguage(FunctionalTester $I)
    {
        $I->sendGet(apiUrl('languages', ['en']));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true,
            ]
        );
    }

    public function shouldGetListOfLanguages(FunctionalTester $I)
    {
        $I->sendGet(apiUrl('languages'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'code'       => 'pl',
                'i18n'       => 'pl_PL',
                'is_enabled' => true,
                'is_default' => false
            ],
            [
                'code'       => 'de',
                'i18n'       => 'de_DE',
                'is_enabled' => false,
                'is_default' => false
            ],
            [
                'code'       => 'fr',
                'i18n'       => 'fr_FR',
                'is_enabled' => false,
                'is_default' => false
            ],
            [

                'code'       => 'en',
                'i18n'       => 'en_US',
                'is_enabled' => true,
                'is_default' => true
            ]
        );
    }
}
