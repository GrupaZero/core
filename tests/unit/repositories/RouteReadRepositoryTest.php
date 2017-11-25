<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Models\Route;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\Repositories\RouteReadRepository;

class RouteReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var RouteReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new RouteReadRepository();
    }

    /** @test */
    public function canAddConditionsToGetMany()
    {
        factory(Route::class, 2)->create(['language_code' => 'en']);
        factory(Route::class)->create(['language_code' => 'en']);
        factory(Route::class)->states('inactive')->create(['language_code' => 'pl']);

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('is_active', '=', true)
                ->where('language_code', '=', 'en')
                ->orderBy('id', 'asc')
        );

        $this->assertEquals(3, $result->count());
        $this->assertEquals('en', $result[0]->language_code);
        $this->assertEquals('en', $result[1]->language_code);
        $this->assertEquals('en', $result[2]->language_code);
        $this->assertTrue($result[0]->is_active);
        $this->assertTrue($result[1]->is_active);
        $this->assertTrue($result[2]->is_active);
    }

    /** @test */
    public function canPaginateResults()
    {
        foreach (range(0, 9) as $key) {
            factory(Route::class)->create(['language_code' => 'en', 'path' => $key . '-example-slug']);
        }

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('is_active', '=', true)
                ->where('language_code', '=', 'en')
                ->orderBy('path', 'desc')
                ->setPageSize(5)
                ->setPage(2)
        );

        $this->assertEquals(5, $result->count());
        $this->assertEquals(5, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals('4-example-slug', $result[0]->path);
        $this->assertEquals('3-example-slug', $result[1]->path);
        $this->assertEquals('2-example-slug', $result[2]->path);
        $this->assertEquals('1-example-slug', $result[3]->path);
        $this->assertEquals('0-example-slug', $result[4]->path);
    }

    /** @test */
    public function canGetByPath()
    {
        factory(Route::class)->create(['language_code' => 'en', 'path' => '0-example-slug']);
        factory(Route::class)->create(['language_code' => 'en', 'path' => '1-example-slug']);
        factory(Route::class)->create(['language_code' => 'pl', 'path' => '0-polish-slug']);
        factory(Route::class)->create(['language_code' => 'pl', 'path' => '1-polish-slug']);

        $first  = $this->repository->getByPath('0-example-slug', 'en');
        $second = $this->repository->getByPath('1-example-slug', 'en');
        $third  = $this->repository->getByPath('not-existing-slug', 'en');

        $this->assertNotNull($first);
        $this->assertEquals('0-example-slug', $first->path);

        $this->assertNotNull($second);
        $this->assertEquals('1-example-slug', $second->path);

        $this->assertNull($third);
    }
}

