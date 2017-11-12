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
        $firstUser  = (new CreateUser('john.doe@example.com', 'secret', null, 'John', 'Doe'))->handle();
        $secondUser = (new CreateUser('zoe.doe@example.com', 'secret', null, 'Zoe', 'Doe'))->handle();

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

