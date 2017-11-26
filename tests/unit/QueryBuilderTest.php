<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Exception;
use Gzero\Core\Query\Condition;
use Gzero\Core\Query\OrderBy;
use Gzero\Core\Query\QueryBuilder;

class QueryBuilderTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var QueryBuilder */
    protected $qb;

    public function _before()
    {
        $this->qb = new QueryBuilder();
    }

    /** @test */
    public function isInstantiable()
    {
        $this->tester->assertInstanceOf(QueryBuilder::class, $this->qb);
    }

    /** @test */
    public function canAddSomeConditions()
    {
        $this->qb
            ->where('x', '=', 1)
            ->where('y', '>=', 2);

        $this->assertCount(2, $this->qb->getFilters());

        /** @var $condition1 Condition */
        /** @var $condition2 Condition */
        $condition1 = $this->qb->getFilters()[0];
        $condition2 = $this->qb->getFilters()[1];

        $this->assertInstanceOf(Condition::class, $condition1);
        $this->assertInstanceOf(Condition::class, $condition2);

        $this->assertEquals('x', $condition1->getName());
        $this->assertEquals('y', $condition2->getName());
        $this->assertEquals('=', $condition1->getOperation());
        $this->assertEquals('>=', $condition2->getOperation());
        $this->assertEquals(1, $condition1->getValue());
        $this->assertEquals(2, $condition2->getValue());
    }

    /** @test */
    public function shouldDisallowToUseUnsupportedOperation()
    {
        try {
            $this->qb
                ->where('x', '=', 1)
                ->where('y', 'unsupported', 2);
        } catch (Exception $exception) {
            $this->assertEquals('Unsupported condition operation', $exception->getMessage());
            return;
        }
        $this->fail('Exception should be thrown');

    }

    /** @test */
    public function canMixConditionWithSorts()
    {
        $this->qb
            ->where('x', '=', 1)
            ->where('y', '!=', 2)
            ->orderBy('x', 'asc')
            ->orderBy('z', 'desc');

        $this->assertCount(2, $this->qb->getSorts());

        /** @var $sort1 OrderBy */
        /** @var $sort2 OrderBy */
        $sort1 = $this->qb->getSorts()[0];
        $sort2 = $this->qb->getSorts()[1];

        $this->assertInstanceOf(OrderBy::class, $sort1);
        $this->assertInstanceOf(OrderBy::class, $sort2);


        $this->assertEquals('x', $sort1->getName());
        $this->assertEquals('z', $sort2->getName());
        $this->assertEquals('asc', $sort1->getDirection());
        $this->assertEquals('desc', $sort2->getDirection());
    }

}
