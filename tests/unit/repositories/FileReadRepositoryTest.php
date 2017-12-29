<?php namespace Cms;

use Codeception\Test\Unit;
use Gzero\Core\Repositories\FileReadRepository;
use Gzero\Core\Query\QueryBuilder;

class FileReadRepositoryTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var FileReadRepository */
    protected $repository;

    protected function _before()
    {
        $this->repository = new FileReadRepository();
    }

    /** @test */
    public function canPaginateResults()
    {
        $this->tester->haveFiles([
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'A title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'B title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'C title'
                    ]
                ]
            ],
            [
                'translations' => [
                    [
                        'language_code' => 'en',
                        'title'         => 'D title'
                    ]
                ]
            ]
        ]);

        $result = $this->repository->getMany(
            (new QueryBuilder)
                ->where('translations.language_code', '=', 'en')
                ->orderBy('translations.title', 'desc')
                ->setPageSize(2)
                ->setPage(2)
        );

        $this->assertEquals(2, $result->count());
        $this->assertEquals(2, $result->perPage());
        $this->assertEquals(2, $result->currentPage());
        $this->assertEquals('B title', $result->first()->translations->first()->title);
        $this->assertEquals('A title', $result->last()->translations->first()->title);
    }
}

