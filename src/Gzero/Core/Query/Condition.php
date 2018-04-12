<?php namespace Gzero\Core\Query;

use Gzero\InvalidArgumentException;
use Illuminate\Database\Eloquent\Builder;

class Condition {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation;

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    public static $allowedOperations = [
        '=',
        '!=',
        '>',
        '>=',
        '<',
        '<=',
        'in',
        'not in',
        'like',
        'not like',
        'between',
        'not between'
    ];

    /** @var array */
    public static $negateOperators = ['!=', 'not in', 'not between', 'not like'];

    /**
     * Condition constructor.
     *
     * @param string $name      Column name
     * @param string $operation Operation
     * @param mixed  $value     Value
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, string $operation, $value)
    {
        if (empty($name)) {
            throw new InvalidArgumentException('Condition: Key must be defined');
        }
        $this->name      = strtolower($name);
        $this->operation = strtolower($operation);
        $this->value     = ($operation === 'in' || $operation === 'not in') ? array_wrap($value) : $value;
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
     * Returns operation
     *
     * @return string
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Return value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
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
     * Checks if this condition was applied to query
     *
     * @return bool
     */
    public function hasBeenApplied(): bool
    {
        return $this->applied;
    }

    /**
     * Check if it's negate operation
     *
     * @return bool
     */
    public function isNegate(): bool
    {
        return in_array($this->operation, self::$negateOperators, true);
    }

    /**
     * Check if it's a null condition case
     *
     * @return bool
     */
    public function isNullCondition(): bool
    {
        return $this->value === null;
    }

    /**
     * Applies condition to Eloquent query builder
     *
     * @param Builder     $query      Eloquent query builder
     * @param string|null $tableAlias SQL table alias
     * @param string|null $customName Override field name
     *
     * @throws InvalidArgumentException
     *
     * @return void
     * @SuppressWarnings(PHPMD)
     */
    public function apply(Builder $query, string $tableAlias = null, string $customName = null)
    {
        if ($this->hasBeenApplied()) {
            return;
        }

        $name = $this->buildDbName($tableAlias, $customName);

        switch ($this->operation) {
            case '=':
            case '!=':
            case '>':
            case '<':
            case '>=':
            case '<=':
                $query->where($name, $this->operation, $this->value);
                break;
            case 'between':
                $query->whereBetween($name, $this->value);
                break;
            case 'not between':
                $query->whereNotBetween($name, $this->value);
                break;
            case 'in':
                $query->whereIn($name, $this->value);
                break;
            case 'not in':
                $query->whereNotIn($name, $this->value);
                break;
            default:
                throw new InvalidArgumentException('Unsupported operation');
        }
        $this->setApplied(true);
    }

    /**
     * Validates operation
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    protected function validate()
    {
        if (!in_array($this->operation, self::$allowedOperations, true)) {
            throw new InvalidArgumentException('Unsupported condition operation');
        }

        if (!is_array($this->value) && $this->isArrayOperation()) {
            throw new InvalidArgumentException('Value is not of type array');
        }

        if (is_array($this->value) && $this->isRangeOperation() && count($this->value) < 2) {
            throw new InvalidArgumentException('Wrong number of values for range');
        }
    }

    /**
     * Check if operation is an array operation
     *
     * @return bool
     */
    protected function isArrayOperation(): bool
    {
        return $this->operation === 'in' || $this->operation === 'not in';
    }

    /**
     * Check if operation is a range operation
     *
     * @return bool
     */
    protected function isRangeOperation(): bool
    {
        return $this->operation === 'between' || $this->operation === 'not between';
    }

    /**
     * It builds db name
     *
     * @param string|null $tableAlias Table alias
     * @param string|null $customName Optional field name to override to
     *
     * @return string
     */
    protected function buildDbName(?string $tableAlias, ?string $customName): string
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';
        $name       = ($customName) ? $tableAlias . $customName : $tableAlias . $this->name;
        return $name;
    }
}
