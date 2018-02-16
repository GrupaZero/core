<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Jobs\CreateUser;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\UserReadRepository;

class UserReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var UserReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new UserReadRepository();
    }

    /** @test */
    public function canSortUsersList()
    {
        $firstUser  = (new CreateUser([
            'email'      => 'john.doe@example.com',
            'password'   => 'secret',
            'name'       => null,
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ]))->handle();
        $secondUser = (new CreateUser([
            'email'      => 'zoe.doe@example.com',
            'password'   => 'secret',
            'name'       => null,
            'first_name' => 'Zoe',
            'last_name'  => 'Doe'
        ]))->handle();

        // ASC
        $result = $this->repository->getMany(
            (new QueryBuilder)->orderBy('email', 'asc')
        );

        $this->assertEquals($result[0]->email, 'admin@gzero.pl');
        $this->assertEquals($result[1]->email, $firstUser->email);
        $this->assertEquals($result[2]->email, $secondUser->email);

        // DESC
        $result = $this->repository->getMany(
            (new QueryBuilder)->orderBy('email', 'desc')
        );

        $this->assertEquals($result[0]->email, $secondUser->email);
        $this->assertEquals($result[1]->email, $firstUser->email);
        $this->assertEquals($result[2]->email, 'admin@gzero.pl');
    }
}

