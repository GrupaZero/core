<?php namespace Core\api;

use Core\FunctionalTester;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class UserCest {

    public function adminShouldBeAbleToGetListOfUsers(FunctionalTester $I)
    {
        $usersNumber = 4;
        $I->loginAsAdmin();
        $I->haveUsers($usersNumber);

        $I->sendGET(apiUrl('users'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(
            [
                'data'  => [
                    [
                        'id'         => 1,
                        'email'      => 'admin@gzero.pl',
                        'name'       => 'Admin',
                        'first_name' => 'John',
                        'last_name'  => 'Doe'
                    ]
                ],
                'meta'  => [
                    'current_page' => 1,
                    'from'         => 1,
                    'last_page'    => 1,
                    'path'         => apiUrl('users'),
                    'per_page'     => 20,
                    'to'           => $usersNumber + 1,
                    'total'        => $usersNumber + 1,
                ],
                'links' => [
                    'first' => apiUrl('users') . '?page=1',
                    'last'  => apiUrl('users') . '?page=1',
                    'prev'  => null,
                    'next'  => null
                ],
            ]
        );
    }

    public function adminShouldBeAbleToFilterListOfUsersByEmail(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveUser([
            'email'      => 'john.doe@example.com',
            'name'       => 'JohnDoe',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $I->sendGET(apiUrl('users?email=john.doe@example.com'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    'email'      => 'john.doe@example.com',
                    'name'       => 'JohnDoe',
                    'first_name' => 'John',
                    'last_name'  => 'Doe'
                ]
            ]
        );

        $I->sendGET(apiUrl('users?email=!john.doe@example.com'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->dontSeeResponseContainsJson(
            [
                'data' => [
                    'email'      => 'john.doe@example.com',
                    'name'       => 'JohnDoe',
                    'first_name' => 'John',
                    'last_name'  => 'Doe'
                ]
            ]
        );
    }

    public function adminShouldNotBeAbleToFilterListOfUsersByInvalidEmail(FunctionalTester $I)
    {
        $I->loginAsAdmin();

        $I->sendGET(apiUrl('users?email=john.doeexample.com'));

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'email' => ["The email must be a valid email address."],
                ]
            ]
        );
    }

    public function adminShouldBeAbleToFilterListOfUsersByCreatedAt(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveUser([
            'email'      => 'john.doe@example.com',
            'name'       => 'JohnDoe',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);

        $I->sendGET(apiUrl('users?created_at=2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl('users?created_at=!2017-10-09,2017-10-10'));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    'email'      => 'john.doe@example.com',
                    'name'       => 'JohnDoe',
                    'first_name' => 'John',
                    'last_name'  => 'Doe'
                ]
            ]
        );
    }

    public function adminShouldBeAbleToFilterListOfUsersByUpdatedAt(FunctionalTester $I)
    {
        $fourDaysAgo = Carbon::now()->subDays(4);
        $today       = Carbon::now()->format('Y-m-d');

        $I->loginAsAdmin();
        $I->haveUser([
            'email'      => 'john.doe@example.com',
            'name'       => 'JohnDoe',
            'first_name' => 'John',
            'last_name'  => 'Doe',
            'updated_at'    => $fourDaysAgo
        ]);

        $I->sendGET(apiUrl("users?updated_at=>$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEmpty($I->grabDataFromResponseByJsonPath('data[*]'));

        $I->sendGET(apiUrl("users?updated_at=<$today"));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    'email'      => 'john.doe@example.com',
                    'name'       => 'JohnDoe',
                    'first_name' => 'John',
                    'last_name'  => 'Doe'
                ]
            ]
        );
    }

    public function adminShouldBeAbleToGetSomeUsersByTheirId(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $john = $I->haveUser([
            'email'      => 'john.doe@example.com',
            'name'       => 'JohnDoe',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ]);
        $jane = $I->haveUser([
            'email'      => 'jane.doe@example.com',
            'name'       => 'JaneDoe',
            'first_name' => 'Jane',
            'last_name'  => 'Doe',
        ]);
        $joe = $I->haveUser([
            'email'      => 'joe.doe@example.com',
            'name'       => 'JoeDoe',
            'first_name' => 'Joe',
            'last_name'  => 'Doe',
        ]);

        $I->sendGET(apiUrl('users?id='.$john->id.','.$jane->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'data' => [
                    [
                        'email'      => 'john.doe@example.com',
                        'name'       => 'JohnDoe',
                        'first_name' => 'John',
                        'last_name'  => 'Doe'
                    ],
                    [
                        'email'      => 'jane.doe@example.com',
                        'name'       => 'JaneDoe',
                        'first_name' => 'Jane',
                        'last_name'  => 'Doe',
                    ]
                ]
            ]
        );

        $I->sendGET(apiUrl('users?id=!'.$joe->id));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('data[*]');
        $I->dontSeeResponseContainsJson(
            [
                'data' => [
                    'email'      => 'joe.doe@example.com',
                    'name'       => 'JoeDoe',
                    'first_name' => 'Joe',
                    'last_name'  => 'Doe',
                ]
            ]
        );
    }

    public function adminShouldBeAbleToSortListOfUsers(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $I->haveUsers([
            [
                'email'      => 'john.doe@example.com',
                'name'       => 'JohnDoe',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ],
            [
                'email'      => 'zoe.doe@example.com',
                'name'       => 'ZoeDoe',
                'first_name' => 'Zoe',
                'last_name'  => 'Doe',
            ]
        ]);

        $I->sendGET(apiUrl('users?sort=-email'));

        $first  = $I->grabDataFromResponseByJsonPath('data[0].email');
        $second = $I->grabDataFromResponseByJsonPath('data[1].email');
        $third  = $I->grabDataFromResponseByJsonPath('data[2].email');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEquals('zoe.doe@example.com', head($first));
        $I->assertEquals('john.doe@example.com', head($second));
        $I->assertEquals('admin@gzero.pl', head($third));

        $I->sendGET(apiUrl('users?sort=email'));

        $first  = $I->grabDataFromResponseByJsonPath('data[0].email');
        $second = $I->grabDataFromResponseByJsonPath('data[1].email');
        $third  = $I->grabDataFromResponseByJsonPath('data[2].email');

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->assertEquals('admin@gzero.pl', head($first));
        $I->assertEquals('john.doe@example.com', head($second));
        $I->assertEquals('zoe.doe@example.com', head($third));
    }

    public function adminShouldBeAbleToGetSingleUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser(
            [
                'name'       => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
                'password'   => Hash::make('test123')
            ]
        );

        $I->sendGet(apiUrl('users', [$user->id]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Test user',
                'first_name' => 'John',
                'last_name'  => 'Doe',
            ]
        );
    }

    public function adminShouldNotBeAbleToGetNonExistingUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();

        $I->sendGET(apiUrl('users', [4]));

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Not found']);
    }

    public function adminShouldBeAbleToUpdateUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser();

        $I->sendPATCH(apiUrl('users', [$user->id]),
            [
                'name'      => 'Modified user',
                'firstName' => 'Johny',
                'lastName'  => 'Stark',
                'email'     => $user->email,
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => $user->email,
            ]
        );
    }

    public function adminShouldNotBeAbleToUpdateNonExistingUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();

        $I->sendPATCH(apiUrl('users', [4]));

        $I->seeResponseCodeIs(404);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['message' => 'Not found']);
    }

    public function adminShouldBeAbleToDeleteUser(FunctionalTester $I)
    {
        $I->loginAsAdmin();
        $user = $I->haveUser();

        $I->sendDELETE(apiUrl('users', [$user->id]));

        $I->seeResponseCodeIs(204);
    }

    public function shouldBeAbleToUpdateMyPersonalInformation(FunctionalTester $I)
    {
        $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => 'newEmail@example.com'
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'name'       => 'Modified user',
                'first_name' => 'Johny',
                'last_name'  => 'Stark',
                'email'      => 'newEmail@example.com',
            ]
        );
    }

    public function shouldBeAbleToUpdateMyPassword(FunctionalTester $I)
    {
        $user = $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'                  => $user->name,
                'email'                 => $user->email,
                'password'              => 'newPassword',
                'password_confirmation' => 'newPassword',
            ]
        );

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');

        $I->deleteHeader('Authorization');
        $I->login($user->email, 'newPassword');
    }

    public function cantChangeMyPasswordWithoutConfirmation(FunctionalTester $I)
    {
        $user = $I->loginAsUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'     => $user->name,
                'email'    => $user->email,
                'password' => 'newPassword',
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'password' => ['The password and password confirmation must match.']
                ]
            ]
        );
    }

    public function cantChangeMyNameToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->loginAsUser();
        $user2 = $I->haveUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'  => $user2->name,
                'email' => $user->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'name' => ['The name has already been taken.']
                ]
            ]
        );
    }

    public function cantChangeMyEmailToAlreadyTaken(FunctionalTester $I)
    {
        $user  = $I->loginAsUser();
        $user2 = $I->haveUser();

        $I->sendPATCH(apiUrl('users/me'),
            [
                'name'  => $user->name,
                'email' => $user2->email,
            ]
        );

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->dontSeeResponseJsonMatchesJsonPath('data[*]');
        $I->seeResponseContainsJson(
            [
                'message' => 'The given data was invalid.',
                'errors'  => [
                    'email' => ['The email has already been taken.']
                ]
            ]
        );
    }

}
