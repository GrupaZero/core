<?php namespace Gzero\Core\Query;

use Gzero\Core\Exception;
use Illuminate\Database\Eloquent\Builder;

class OrderBy {

    /** @var string */
    protected $name;

    /** @var string */
    protected $direction;

    /** @var array */
    public static $allowedOperations = [
        'asc',
        'desc'
    ];

    /**
     * OrderBy constructor.
     *
     * @param string $name      Column name
     * @param string $direction Direction
     *
     * @throws Exception
     */
    public function __construct(string $name, string $direction)
    {
        if (empty($name)) {
            throw new Exception('OrderBy: Name must be defined');
        }
        $this->name      = strtolower($name);
        $this->direction = strtolower($direction);
        $this->validate();
    }

    /**
     * Returns name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return direction
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }

    /**
     * Applies orderBy on Eloquent query builder
     *
     * @param Builder     $query      Eloquent query builder
     * @param string|null $tableAlias SQL table alias
     *
     * @return void
     */
    public function apply(Builder $query, $tableAlias = null)
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';
        $query->orderBy($tableAlias . $this->name, $this->direction);
    }

    /**
     * Validates
     *
     * @throws Exception
     * @return void
     */
    protected function validate()
    {
        if (!in_array($this->direction, self::$allowedOperations, true)) {
            throw new Exception('Unsupported orderBy operation');
        }
    }

}
