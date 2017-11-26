<?php namespace Core;

use Codeception\Test\Unit;
use Gzero\Core\Exception;
use Gzero\Core\Query\Condition;
use Gzero\Core\Query\OrderBy;
use Gzero\Core\Query\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Mockery;

class QueryBuilderTest extends Unit {

    /** @var UnitTester */
    protected $tester;

    /** @var QueryBuilder */
    protected $qb;

    public function _before()
    {
        $this->qb = new QueryBuilder();
    }

    public function _after()
    {
        Mockery::close();
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

    /** @test */
    public function itUsesAppliedPropertyCorrectly()
    {
        $mock = Mockery::mock(Builder::class)
            ->shouldReceive('where')
            ->with('name', '!=', 'value')
            ->once()
            ->shouldReceive('orderBy')
            ->with('name', 'asc')
            ->once()
            ->getMock();

        $this->qb
            ->where('name', '!=', 'value')
            ->orderBy('name', 'asc');

        $this->qb->applyFilters($mock);
        $this->qb->applySorts($mock);

        $filter = $this->qb->getFilter('name');
        $sort   = $this->qb->getSort('name');

        $this->assertTrue($filter->hasBeenApplied());
        $this->assertTrue($sort->hasBeenApplied());

        // It shouldn't apply on next query
        $mock2 = Mockery::mock(Builder::class)
            ->shouldNotReceive('where')
            ->getMock();

        $this->qb->applyFilters($mock2);
        $this->qb->applySorts($mock2);

        // Re-apply
        $filter->setApplied(false);
        $sort->setApplied(false);

        $mock3 = Mockery::mock(Builder::class)
            ->shouldReceive('where')
            ->with('name', '!=', 'value')
            ->once()
            ->shouldReceive('orderBy')
            ->with('name', 'asc')
            ->once()
            ->getMock();

        $this->qb->applyFilters($mock3);
        $this->qb->applySorts($mock3);
    }

    /** @test */
    public function itUsesAppliedPropertyOnRelationsCorrectly()
    {
        $this->qb
            ->where('relation.name', 'between', [100, 200])
            ->orderBy('relation.nested.other_field', 'asc');

        $filter = $this->qb->getFilter('relation.name');
        $sort   = $this->qb->getSort('relation.nested.other_field');


        $mock = Mockery::mock(Builder::class)
            ->shouldReceive('whereBetween')
            ->with('r.name', [100, 200])
            ->once()
            ->shouldReceive('orderBy')
            ->with('rn.other_field', 'asc')
            ->once()
            ->getMock();

        $this->qb->applyFilters($mock);
        $this->qb->applySorts($mock);

        // Filters should be present
        $this->assertTrue($this->qb->hasFilter('relation.name'));
        $this->assertTrue($this->qb->hasSort('relation.nested.other_field'));

        // Should not add those filters on top level
        $this->assertNull($this->qb->getFilter('name'));
        $this->assertNull($this->qb->getSort('other_field'));
        $this->assertFalse($this->qb->hasFilter('name'));
        $this->assertFalse($this->qb->hasSort('other_field'));

        // Filters shouldn't be applied on top level
        $this->assertFalse($filter->hasBeenApplied());
        $this->assertFalse($sort->hasBeenApplied());

        $this->qb->applyRelationFilters('relation', 'r', $mock);
        $this->qb->applyRelationSorts('relation.nested', 'rn', $mock);

        // Filters should be applied now
        $this->assertTrue($filter->hasBeenApplied());
        $this->assertTrue($sort->hasBeenApplied());


        // It shouldn't apply on next query
        $mock2 = Mockery::mock(Builder::class)
            ->shouldNotReceive('whereBetween', 'orderBy')
            ->getMock();

        $this->qb->applyRelationFilters('relation', 'r', $mock2);
        $this->qb->applyRelationSorts('relation.nested', 'rn', $mock2);


        // Re-apply
        $filter->setApplied(false);
        $sort->setApplied(false);

        // Apply on another query
        $mock3 = Mockery::mock(Builder::class)
            ->shouldReceive('whereBetween')
            ->with('r.name', [100, 200])
            ->once()
            ->shouldReceive('orderBy')
            ->with('rn.other_field', 'asc')
            ->once()
            ->getMock();

        $this->qb->applyRelationFilters('relation', 'r', $mock3);
        $this->qb->applyRelationSorts('relation.nested', 'rn', $mock3);
    }

    /** @test */
    public function canManuallyImplementFilterOrSort()
    {
        $this->qb
            ->where('relation.name', 'between', [100, 200])
            ->orderBy('relation.nested.other_field', 'asc');

        $mock = Mockery::mock(Builder::class)
            ->shouldReceive('whereBetween')
            ->with('r.test', [100, 200])
            ->once()
            ->shouldReceive('orderBy')
            ->with('rn.test2', 'asc')
            ->once()
            ->getMock();

        $filter = $this->qb->getFilter('relation.name');
        $sort   = $this->qb->getSort('relation.nested.other_field');

        $filter->apply($mock, 'r', 'test');
        $sort->apply($mock, 'rn', 'test2');

        $this->assertTrue($filter->hasBeenApplied());
        $this->assertTrue($sort->hasBeenApplied());
    }

}
