<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Parsers\BoolParser;
use Gzero\Core\Parsers\NumericParser;
use Gzero\Core\Query\Condition;
use Gzero\Core\Query\QueryBuilder;
use Gzero\Core\UrlParamsProcessor;
use Gzero\Core\Query\OrderBy;
use Gzero\Core\Parsers\StringParser;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UrlParamsProcessorTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var UrlParamsProcessor */
    protected $processor;

    public function _before()
    {
        $this->processor = new UrlParamsProcessor(resolve('Illuminate\Contracts\Validation\Factory'));
    }

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(UrlParamsProcessor::class, $this->processor);
    }

    /** @test */
    public function canRegisterParsers()
    {
        $this->processor
            ->addFilter(new StringParser('translations.url'))
            ->addFilter(new StringParser('translations.language_code'))
            ->process(new Request([
                'sort'         => '-test1,test2,author.created_at',
                'page'         => 3,
                'per_page'     => 21,
                'translations' => [
                    'language_code' => 'en',
                    'url'           => 'awesome-url'
                ]
            ]));
    }

    /** @test */
    public function shouldMergeValidationRulesFromFilter()
    {
        try {
            $this->processor
                ->addFilter(new StringParser('translations.language_code'), 'required_with:translations.url|string')
                ->process(new Request([
                    'sort'         => '-test1,test2,author.created_at',
                    'page'         => 3,
                    'per_page'     => 21,
                    'translations' => [
                        'url' => 'awesome-url'
                    ]
                ]));
        } catch (ValidationException $exception) {
            $this->assertEquals(
                [
                    'translations.language_code' => [
                        'The translations.language code field is required when translations.url is present.'
                    ]
                ],
                $exception->errors());
            return;
        }
        $this->fail('Exception should be thrown');
    }

    /** @test */
    public function shouldReturnParsers()
    {
        $this->processor
            ->addFilter(new StringParser('translations.url'))
            ->process(new Request([
                'sort'         => '-test1,test2,author.created_at',
                'page'         => 3,
                'per_page'     => 21,
                'translations' => [
                    'language_code' => 'en',
                    'url'           => 'awesome-url'
                ]
            ]));

        $this->tester->assertCount(1, $this->processor->getParsers());
        $this->tester->assertInstanceOf(StringParser::class, $this->processor->getParsers()[0]);
    }

    /** @test */
    public function shouldReturnSorts()
    {
        $this->processor
            ->process(new Request([
                'sort' => '-test1,test2,author.created_at'
            ]));

        $this->tester->assertCount(3, $this->processor->getSorts());
        $this->tester->assertEquals(
            [
                ['test1', 'DESC'],
                ['test2', 'ASC'],
                ['author.created_at', 'ASC']
            ],
            $this->processor->getSorts());
    }

    /** @test */
    public function canProcessSearchQuery()
    {
        $this->processor->process(new Request(['q' => 'Lore Ipsum']));

        $this->tester->assertEquals(
            $this->processor->getSearchQuery(),
            'Lore Ipsum'
        );
    }

    /** @test */
    public function isReturningPageParams()
    {
        $this->processor->process(new Request(['page' => 3, 'per_page' => 21]));
        $builder = $this->processor->buildQueryBuilder();

        $this->tester->assertEquals($this->processor->getPage(), 3);
        $this->tester->assertEquals($this->processor->getPerPage(), 21);
        $this->tester->assertEquals(3, $builder->getPage());
        $this->tester->assertEquals(21, $builder->getPageSize());

        $this->processor->reset();

        $this->processor->process(new Request(['page' => 3]));
        $builder = $this->processor->buildQueryBuilder();

        $this->tester->assertEquals($this->processor->getPage(), 3);
        $this->tester->assertNull($this->processor->getPerPage());
        $this->tester->assertEquals(3, $builder->getPage());
        $this->tester->assertEquals(QueryBuilder::ITEMS_PER_PAGE, $builder->getPageSize());
    }

    /** @test */
    public function shouldReturnQueryBuilderWithCorrectOrderBy()
    {
        $this->processor->process(new Request(['sort' => '-test1,test2,author.created_at']));

        $query = $this->processor->buildQueryBuilder();

        $this->tester->assertEquals([new OrderBy('test1', 'DESC'), new OrderBy('test2', 'ASC')], $query->getSorts());
        $this->tester->assertEquals([new OrderBy('created_at', 'ASC')], $query->getRelationSorts('author'));
        $this->tester->assertEquals(new OrderBy('created_at', 'ASC'), $query->getSort('author.created_at'));
    }

    /** @test */
    public function shouldReturnQueryBuilderWithCorrectFilters()
    {
        $this->processor
            ->addFilter(new StringParser('not_required_filter'))
            ->addFilter(new StringParser('lang'))
            ->addFilter(new StringParser('test2'), 'required')
            ->addFilter(new StringParser('translation.language_code'))
            ->addFilter(new NumericParser('translation.number'))
            ->addFilter(new BoolParser('translation.bool'))
            ->process(new Request([
                'lang'        => 'en',
                'test2'       => 'test2',
                'translation' => ['language_code' => 'en', 'number' => 1, 'bool' => 'false']
            ]));

        $query = $this->processor->buildQueryBuilder();

        $this->assertEquals(
            [new Condition('lang', '=', 'en'), new Condition('test2', '=', 'test2')],
            $query->getFilters()
        );
        $this->assertEquals(
            [
                new Condition('language_code', '=', 'en'),
                new Condition('number', '=', 1),
                new Condition('bool', '=', false),
            ],
            $query->getRelationFilters('translation')
        );
        $this->assertEquals(
            new Condition('language_code', '=', 'en'),
            $query->getFilter('translation.language_code')
        );
    }
}
