<?php namespace Gzero\Core\Query;

use Gzero\Core\Exception;
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
     * It returns single filter by field name
     *
     * @param string $fieldName Condition field name
     *
     * @return Condition
     */
    public function getFilter(string $fieldName): ?Condition
    {
        [$name, $filters] = $this->getArrayAndKeyName($fieldName, 'filters');

        return array_first($filters, function ($filter) use ($name) {
            return $filter->getName() === $name;
        });
    }

    /**
     * Checks if specific filter was used during building query
     *
     * @param string $fieldName Relation name
     *
     * @return bool
     */
    public function hasFilter(string $fieldName): bool
    {
        return !!$this->getFilter($fieldName);
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
     * It returns single sort by field name
     *
     * @param string $sortName Sort field name
     *
     * @return OrderBy
     */
    public function getSort($sortName): ?OrderBy
    {
        [$name, $sorts] = $this->getArrayAndKeyName($sortName, 'sorts');

        return array_first($sorts, function ($sort) use ($name) {
            return $sort->getName() === $name;
        });
    }

    /**
     * Checks if specific sort was used during building query
     *
     * @param string $sortName Relation name
     *
     * @return bool
     */
    public function hasSort(string $sortName): bool
    {
        return !!$this->getSort($sortName);
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
            $result              = array_get($this->relations, $relationPath, ['filters' => [], 'sorts' => []]);
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
            $result            = array_get($this->relations, $relationPath, ['filters' => [], 'sorts' => []]);
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

    /**
     * Helper function to figure out which array to use
     *
     * @param string $fieldName Field name
     * @param string $key       filters or sorts
     *
     * @throws Exception
     *
     * @return array
     */
    protected function getArrayAndKeyName(string $fieldName, string $key): array
    {
        if (!in_array($key, ['filters', 'sorts'], true)) {
            throw new Exception('Key must be one of [filters, sorts]');
        }
        if (str_contains($fieldName, '.')) {
            $path         = explode('.', $fieldName);
            $name         = array_pop($path);
            $relationPath = implode('.', $path);
            $array        = array_get($this->relations, "$relationPath.$key", []);
        } else {
            $name  = $fieldName;
            $array = ($key === 'filters') ? $this->filters : $this->sorts;
        }
        return [$name, $array];
    }

}
