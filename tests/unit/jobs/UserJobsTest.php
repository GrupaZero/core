<?php namespace Core;

use Codeception\Test\Unit;
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
        $user       = (new CreateUser('john.doe@example.com', 'secret', 'Nickname', 'John', 'Doe'))->handle();
        $userFromDb = $this->repository->getById($user->id);

        $this->assertEquals(
            [
                $user->email,
                $user->id,
                $user->name,
                $user->first_name,
                $user->last_name
            ],
            [
                $userFromDb->email,
                $userFromDb->id,
                $userFromDb->name,
                $userFromDb->first_name,
                $userFromDb->last_name
            ]
        );
    }

    /** @test */
    public function canCreateUserWithEmptyNameAsAnonymous()
    {
        $user1 = (new CreateUser('john.doe@example.com', 'secret', '', 'John', 'Doe'))->handle();
        $user2 = (new CreateUser('jane.doe@example.com', 'secret', '', 'Jane', 'Doe'))->handle();

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
        (new DeleteUser($user1))->handle();

        $user3   = (new CreateUser('jane.doe2@example.com', 'secret', '', 'Jane', 'Doe2'))->handle();
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
        $user = (new CreateUser('john.doe@example.com', 'password', '', 'John', 'Doe'))->handle();

        $user = (new UpdateUser($user, ['password' => 'secret']))->handle();

        $this->assertTrue(Hash::check('secret', $user->password));
    }

    /** @test */
    public function canDeleteUser()
    {
        $user       = (new CreateUser('john.doe@example.com', 'secret', 'Nickname', 'John', 'Doe'))->handle();
        $userFromDb = $this->repository->getById($user->id);

        $this->assertNotNull($userFromDb);
        $this->assertNotNull(User::where([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => 'Nickname',
            'first_name' => 'John',
            'last_name'  => 'Doe',
        ])->first());

        (new DeleteUser($user))->handle();

        $userFromDb = $this->repository->getById($user->id);

        $this->assertNull($userFromDb);
    }

}

