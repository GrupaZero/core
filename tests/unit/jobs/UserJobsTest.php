<?php namespace Core;

use function array_merge;
use function array_only;
use Codeception\Test\Unit;
use function delete;
use function dispatch_now;
use Gzero\Core\Jobs\CreateUser;
use Gzero\Core\Jobs\DeleteUser;
use Gzero\Core\Jobs\UpdateUser;
use Gzero\Core\Models\User;
use Gzero\Core\Repositories\UserReadRepository;
use Illuminate\Support\Facades\Hash;

class UserJobsTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var UserReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new UserReadRepository();
    }

    /** @test */
    public function canCreateUserAndGetItById()
    {
        $testedAttribues = [
            'email',
            'name',
            'first_name',
            'last_name',
            'language_code',
            'timezone'
        ];
        $data            = [
            'email'         => 'john.doe@example.com',
            'password'      => 'secret',
            'name'          => 'Nickname',
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'language_code' => 'pl',
            'timezone'      => 'Africa/Algiers'
        ];

        $user       = dispatch_now(new CreateUser($data));
        $userFromDb = $this->repository->getById($user->id);

        $this->assertEquals(
            array_only($data, $testedAttribues),
            array_only($user->attributesToArray(), $testedAttribues)
        );

        $this->assertEquals(
            array_only($user->attributesToArray(), $testedAttribues),
            array_only($userFromDb->attributesToArray(), $testedAttribues)
        );
    }

    /** @test */
    public function canCreateUserWithEmptyNameAsAnonymous()
    {
        $user1 = dispatch_now(new CreateUser([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => '',
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ]));
        $user2 = dispatch_now(new CreateUser([
            'email'      => 'jane.doe@example.com',
            'password'   => 'secret',
            'name'       => '',
            'first_name' => 'Jane',
            'last_name'  => 'Doe'
        ]));

        $user1Db = $this->repository->getById($user1->id);
        $user2Db = $this->repository->getById($user2->id);

        $this->assertEquals(
            [
                $user1->email,
                $user1->id,
                $user1->first_name,
                $user1->last_name
            ],
            [
                $user1Db->email,
                $user1Db->id,
                $user1Db->first_name,
                $user1Db->last_name
            ]
        );

        $this->assertEquals(
            [
                $user2->email,
                $user2->id,
                $user2->first_name,
                $user2->last_name
            ],
            [
                $user2Db->email,
                $user2Db->id,
                $user2Db->first_name,
                $user2Db->last_name
            ]
        );

        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user1Db->name);
        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user2Db->name);

        // Deleting user1 to make sure that we still return unique name
        dispatch_now(new DeleteUser($user1));

        $user3   = dispatch_now(new CreateUser([
            'email'      => 'jane.doe2@example.com',
            'password'   => 'secret',
            'name'       => '',
            'first_name' => 'Jane',
            'last_name'  => 'Doe2'
        ]));
        $user3Db = $this->repository->getById($user3->id);

        $this->assertEquals(
            [
                $user3->email,
                $user3->id,
                $user3->first_name,
                $user3->last_name
            ],
            [
                $user3Db->email,
                $user3Db->id,
                $user3Db->first_name,
                $user3Db->last_name
            ]
        );

        $this->assertRegExp('/^anonymous\-[a-z 0-9]{13}/', $user3Db->name);
        $this->assertCount(3, array_unique([$user1Db->name, $user2Db->name, $user3Db->name]));
    }

    /** @test */
    public function itHashesUserPasswordWhenUpdatingUser()
    {
        $user = dispatch_now(new CreateUser([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => '',
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ]));

        $user = dispatch_now(new UpdateUser($user, ['password' => 'secret2']));

        $this->assertTrue(Hash::check('secret2', $user->password));
    }

    /** @test */
    public function itHashesUserPasswordWhenCreatingUser()
    {
        $user       = dispatch_now(new CreateUser([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => '',
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ]));
        $userFromDb = $this->repository->getById($user->id);

        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertTrue(Hash::check('secret', $userFromDb->password));
    }

    /** @test */
    public function canDeleteUser()
    {
        $user       = dispatch_now(new CreateUser([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => 'Nickname',
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ]));
        $userFromDb = $this->repository->getById($user->id);

        $this->assertNotNull($userFromDb);
        $this->assertNotNull(User::where([
            'email'      => 'john.doe@example.com',
            'name'       => 'Nickname',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ])->first());

        dispatch_now(new DeleteUser($user));

        $userFromDb = $this->repository->getById($user->id);

        $this->assertNull($userFromDb);
    }

    /** @test */
    public function canUpdateUser()
    {
        $user = dispatch_now(new CreateUser([
            'email'         => 'john.doe@example.com',
            'password'      => 'secret',
            'name'          => '',
            'first_name'    => 'John',
            'last_name'     => 'Doe',
            'language_code' => 'pl',
            'timezone'      => 'Aftica/Algiers'
        ]));

        $afterUser = dispatch_now(new UpdateUser($user, [
            'email'         => 'johnny.mnemonic@example.com',
            'name'          => 'Super Johnny',
            'first_name'    => 'Johnny',
            'last_name'     => 'Mnemonic',
            'language_code' => 'en',
            'timezone'      => 'Africa/Dakar'
        ]));

        $this->assertEquals($user->id, $afterUser->id);

        $userFromDb = $this->repository->getById($user->id);

        $testedAttributes = ['email', 'name', 'first_name', 'last_name', 'language_code', 'timezone'];
        $this->assertEquals(
            [
                'email'         => 'johnny.mnemonic@example.com',
                'name'          => 'Super Johnny',
                'first_name'    => 'Johnny',
                'last_name'     => 'Mnemonic',
                'language_code' => 'en',
                'timezone'      => 'Africa/Dakar'
            ],
            array_only($afterUser->attributesToArray(), $testedAttributes));
        $this->assertEquals(
            array_only($afterUser->attributesToArray(), $testedAttributes),
            array_only($userFromDb->attributesToArray(), $testedAttributes)
        );
    }
}

