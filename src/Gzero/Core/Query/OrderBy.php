<?php namespace Gzero\Core\Query;

use Gzero\Core\Exception;
use Illuminate\Database\Eloquent\Builder;

class OrderBy {

    /** @var string */
    protected $name;

    /** @var string */
    protected $direction;

    /** @var bool */
    protected $applied = false;

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
     * It sets applied property
     *
     * @param bool $value True or false
     *
     * @return void
     */
    public function setApplied(bool $value)
    {
        $this->applied = $value;
    }

    /**
     * Checks if this sort was applied to query
     *
     * @return bool
     */
    public function hasBeenApplied(): bool
    {
        return $this->applied;
    }

    /**
     * Applies orderBy on Eloquent query builder
     *
     * @param Builder     $query      Eloquent query builder
     * @param string|null $tableAlias SQL table alias
     * @param string|null $customName Override field name
     *
     * @return void
     */
    public function apply(Builder $query, string $tableAlias = null, string $customName = null)
    {
        if ($this->hasBeenApplied()) {
            return;
        }

        $name = $this->buildDbName($tableAlias, $customName);

        $query->orderBy($name, $this->direction);
        $this->setApplied(true);
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

    /**
     * It build db name
     *
     * @param string|null $tableAlias Table alias
     * @param string|null $customName Optional field name to override to
     *
     * @return string
     */
    protected function buildDbName(?string $tableAlias, ?string $customName = null): string
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';
        $name       = ($customName) ? $tableAlias . $customName : $tableAlias . $this->name;
        return $name;
    }

}
