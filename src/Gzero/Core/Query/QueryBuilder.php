<?php namespace Gzero\Core\Query;

use Illuminate\Database\Eloquent\Builder;

class QueryBuilder {

    /** Default number of items per page */
    const ITEMS_PER_PAGE = 20;

    /** @var array */
    protected $relations = [];

    /** @var array */
    protected $filters = [];

    /** @var array */
    protected $sorts = [];

    /** @var string */
    protected $searchQuery;

    /** @var int */
    protected $page;

    /** @var int */
    protected $pageSize;

    /**
     * It returns all filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * It returns all sorts
     *
     * @return array
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     * It sets search query
     *
     * @param string $search Search string
     *
     * @return void
     */
    public function setSearchQuery(string $search)
    {
        $this->searchQuery = $search;
    }

    /**
     * Set page size
     *
     * @param int $pageSize Page size
     *
     * @return QueryBuilder
     */
    public function setPageSize(int $pageSize)
    {
        $this->pageSize = $pageSize;
        return $this;
    }

    /**
     * Set page
     *
     * @param int $page Page
     *
     * @return QueryBuilder
     */
    public function setPage(int $page)
    {
        $this->page = $page;
        return $this;
    }

    /**
     * Checks if search query is present
     *
     * @return bool
     */
    public function hasSearchQuery()
    {
        return (bool) $this->searchQuery;
    }

    /**
     * Get search query
     *
     * @return string
     */
    public function getSearchQuery()
    {
        return $this->searchQuery;
    }

    /**
     * Get page
     *
     * @return int
     */
    public function getPage(): int
    {
        return ($this->page) ?: 1;
    }

    /**
     * Get page size
     *
     * @return int
     */
    public function getPageSize(): int
    {
        return ($this->pageSize) ?: self::ITEMS_PER_PAGE;
    }

    /**
     * It adds where condition
     *
     * @param string $key       Column name
     * @param string $operation Operation
     * @param mixed  $value     Value
     *
     * @return $this
     */
    public function where(string $key, string $operation, $value)
    {
        if (str_contains($key, '.')) {
            $fullPath            = explode('.', $key);
            $relationPath        = implode('.', array_slice($fullPath, 0, -1));
            $relationKey         = last($fullPath);
            $result              = array_get($this->relations, $relationPath, ['filters' => [], 'sort' => []]);
            $result['filters'][] = new Condition($relationKey, $operation, $value);
            array_set($this->relations, $relationPath, $result);
        } else {
            $this->filters[] = new Condition($key, $operation, $value);
        }
        return $this;
    }

    /**
     * It adds order by
     *
     * @param string $key       Column name
     * @param string $direction Direction
     *
     * @return $this
     */
    public function orderBy(string $key, string $direction)
    {
        if (str_contains($key, '.')) {
            $fullPath          = explode('.', $key);
            $relationPath      = implode('.', array_slice($fullPath, 0, -1));
            $relationKey       = last($fullPath);
            $result            = array_get($this->relations, $relationPath, ['filters' => [], 'sort' => []]);
            $result['sorts'][] = new OrderBy($relationKey, $direction);
            array_set($this->relations, $relationPath, $result);
        } else {
            $this->sorts[] = new OrderBy($key, $direction);
        }
        return $this;
    }

    /**
     * Checks if specific relation was used during building query
     *
     * @param string $name Relation name
     *
     * @return bool
     */
    public function hasRelation(string $name): bool
    {
        return array_has($this->relations, $name);
    }

    /**
     * Return all filters for specific relation
     *
     * @param string $name Relation name
     *
     * @return array
     */
    public function getRelationFilters(string $name): array
    {
        return array_get($this->relations, $name . '.filters', []);
    }

    /**
     * Returns filter for specific relation
     *
     * @param string $relationName Relation name
     * @param string $filterName   Filter name
     *
     * @return Condition|null
     */
    public function getRelationFilter(string $relationName, string $filterName)
    {
        if (!$this->hasRelation($relationName)) {
            return null;
        }
        return array_first($this->relations[$relationName]['filters'], function ($filter) use ($filterName) {
            return $filter->getName() === $filterName;
        });
    }

    /**
     * Returns all sorts for specific relation
     *
     * @param string $name Relation name
     *
     * @return array
     */
    public function getRelationSorts(string $name): array
    {
        return array_get($this->relations, $name . '.sorts', []);
    }

    /**
     * Returns sort for specific relation
     *
     * @param string $relationName Relation name
     * @param string $sortName     Sort name
     *
     * @return OrderBy|null
     */
    public function getRelationSort(string $relationName, string $sortName)
    {
        if (!$this->hasRelation($relationName)) {
            return null;
        }
        return array_first($this->relations[$relationName]['sorts'], function ($sort) use ($sortName) {
            return $sort->getName() === $sortName;
        });
    }

    /**
     * Applies filter to Eloquent Query builder
     *
     * @param Builder $query Eloquent query builder
     *
     * @return void
     */
    public function applyFilters(Builder $query)
    {
        foreach ($this->getFilters() as $filter) {
            $filter->apply($query);
        }
    }

    /**
     * Applies filters Eloquent Query builder for relation
     *
     * @param string  $relationName Relation name
     * @param string  $alias        SQL alias
     * @param Builder $query        Eloquent query builder
     *
     * @return void
     */
    public function applyRelationFilters(string $relationName, string $alias, Builder $query)
    {
        foreach ($this->getRelationFilters($relationName) as $filter) {
            $filter->apply($query, $alias);
        }
    }

    /**
     * Applies sorts Eloquent Query builder
     *
     * @param Builder $query Eloquent query builder
     *
     * @return void
     */
    public function applySorts(Builder $query)
    {
        foreach ($this->getSorts() as $sort) {
            $sort->apply($query);
        }
    }

    /**
     * Applies sorts Eloquent Query builder for relation
     *
     * @param string  $relationName Relation name
     * @param string  $alias        SQL alias
     * @param Builder $query        Eloquent query builder
     *
     * @return void
     */
    public function applyRelationSorts(string $relationName, string $alias, Builder $query)
    {
        foreach ($this->getRelationSorts($relationName) as $sorts) {
            $sorts->apply($query, $alias);
        }
    }

}
