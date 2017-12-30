<?php namespace Core\api;

use Carbon\Carbon;
use Core\FunctionalTester;
use Gzero\Core\Jobs\AddFileTranslation;
use Gzero\Core\Models\Language;
use Gzero\Core\Models\User;

class FileCest {

    public function _before(FunctionalTester $I)
    {
        $I->loginAsAdmin();
    }

    public function shouldGetListOfFiles(FunctionalTester $I)
    {
        $user = factory(User::class)->create();
        $en   = new Language(['code' => 'en']);
        $pl   = new Language(['code' => 'pl']);

        $block = dispatch_now(CreateFile::basic('New Block', $en, $user, [
            'region'        => 'region',
            'theme'         => 'theme',
            'weight'        => 10,
            'filter'        => 'filter',
            'options'       => 'options',
            'body'          => 'Body',
            'custom_fields' => 'Custom fields',
            'is_active'     => true,
            'is_cacheable'  => true
        ]));

        dispatch_now(new AddFileTranslation($block, 'Nowy Blok', $pl, $user,
            [
                'body'          => 'Treść',
                'custom_fields' => 'Pola'
            ]
        ));

        $I->sendGet(apiUrl('blocks'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                [
                    'type'         => 'basic',
                    'region'       => 'region',
                    'theme'        => 'theme',
                    'weight'       => 10,
                    'filter'       => 'filter',
                    'options'      => 'options',
                    'is_active'    => true,
                    'is_cacheable' => true,
                    'translations' => [
                        [
                            'language_code' => 'en',
                            'title'         => 'New Block',
                            'body'          => 'Body',
                            'custom_fields' => 'Custom fields',
                            'is_active'     => true,
                        ],
                        [
                            'language_code' => 'pl',
                            'title'         => 'Nowy Blok',
                            'body'          => 'Treść',
                            'custom_fields' => 'Pola',
                            'is_active'     => true,
                        ]
                    ]
                ]
            ]
        );
    }

}
