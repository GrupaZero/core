<?php namespace Gzero\Core\Parsers;

use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class DateParser implements ConditionParser {

    /** @var string */
    protected $name;

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    protected $availableOperations = ['!', '>=', '<=', '<', '>'];

    /** @var array */
    protected $option;

    /**
     * @param string $name    Field name
     *
     * @param array  $options Optional array of options
     *
     * @throws InvalidArgumentException
     */
    public function __construct(string $name, $options = [])
    {
        if (empty($name)) {
            throw new InvalidArgumentException('DateParser: Name must be defined');
        }
        $this->name   = $name;
        $this->option = $options;
    }

    /**
     * It returns field name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * It returns operation
     *
     * @return string
     */
    public function getOperation(): string
    {
        return $this->operation;
    }

    /**
     * It returns value
     *
     * @return mixed|null
     */
    public function getValue()
    {
        return ($this->value) ?: null;
    }

    /**
     * Checks if field was present in response during parse phase
     *
     * @return bool
     */
    public function wasApplied(): bool
    {
        return $this->applied;
    }

    /**
     * It parses request field
     *
     * @param Request $request Request object
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function parse(Request $request)
    {
        if ($request->has($this->name)) {
            $this->applied = true;
            $value         = $request->input($this->name);

            // do not reorder this
            if (substr($value, 0, 1) === '!') {
                $this->operation = '!=';
                $this->value = substr($value, 1);
            } elseif (substr($value, 0, 2) === '>=') {
                $this->operation = '>=';
                $this->value     = substr($value, 2);
            } elseif (substr($value, 0, 2) === '<=') {
                $this->operation = '<=';
                $this->value     = substr($value, 2);
            } elseif (substr($value, 0, 1) === '>') {
                $this->operation = '>';
                $this->value     = substr($value, 1);
            } elseif (substr($value, 0, 1) === '<') {
                $this->operation = '<';
                $this->value     = substr($value, 1);
            } else {
                $this->operation = '=';
                $this->value = $value;
            }

            $this->checkValue();
        }
    }

    /**
     * It returns validation rules for this type
     *
     * @return string
     */
    public function getValidationRule()
    {
        return 'regex:/^(!|<=|>=|=|<|>)?\d{4}-\d{2}-\d{2}$/';
    }

    /**
     * It returns query builder that can be pass further to read repository
     *
     * @param QueryBuilder $builder Query builder
     *
     * @return void
     */
    public function apply(QueryBuilder $builder)
    {
        $builder->where($this->name, $this->operation, $this->value);
    }

    /**
     * Check if value is a valid date.
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    protected function checkValue(): void
    {
        if ((!is_string($this->value) && !is_numeric($this->value)) || strtotime($this->value) === false) {
            throw new InvalidArgumentException('DateParser: Value must be a valid date');
        }
    }
}
