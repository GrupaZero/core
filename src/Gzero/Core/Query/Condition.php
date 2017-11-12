<?php namespace Gzero\Core\Query;

use Illuminate\Database\Eloquent\Builder;

class Condition {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation;

    /** @var mixed */
    protected $value;

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
     * @throws Exception
     */
    public function __construct(string $name, string $operation, $value)
    {
        if (empty($name)) {
            throw new Exception('Condition: Key must be defined');
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
     *
     * @throws Exception
     * @return void
     */
    public function apply(Builder $query, string $tableAlias = null)
    {
        $tableAlias = ($tableAlias != null) ? str_finish($tableAlias, '.') : '';

        switch ($this->operation) {
            case '=':
            case '!=':
                $query->where($tableAlias . $this->name, $this->operation, $this->value);
                break;
            case 'between':
                $query->whereBetween($tableAlias . $this->name, $this->value);
                break;
            case 'not between':
                $query->whereNotBetween($tableAlias . $this->name, $this->value);
                break;
            default:
                throw new Exception('Unsupported operation');
        }
    }

    /**
     * Validates operation
     *
     * @throws Exception
     * @return void
     */
    protected function validate()
    {
        if (!in_array($this->operation, self::$allowedOperations, true)) {
            throw new Exception('Unsupported condition operation');
        }
        if (is_array($this->value) && !$this->isCorrectRangeFormat()) {
            throw new Exception('Wrong number of values for range');
        }
    }

    /**
     * Checks if it's correct range format
     *
     * @return bool
     */
    protected function isCorrectRangeFormat(): bool
    {
        return ($this->operation === 'between' || $this->operation === 'not between')
            && count($this->value) === 2;
    }
}
