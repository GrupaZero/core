<?php namespace Gzero\Core\Parsers;

use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class ArrayParser implements ConditionParser {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation = 'in';

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    protected $availableOperations = ['in', 'notIn'];

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
            throw new InvalidArgumentException('ArrayParser: Name must be defined');
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

            if (substr($value, 0, 5) === 'notIn') {
                $this->operation = 'notIn';

                $stringArray = substr($value, 5);
                $this->checkBrackets($stringArray);

                $this->value = explode(',', substr($stringArray, 1, -1));
            } elseif (substr($value, 0, 2) === 'in') {
                $stringArray = substr($value, 2);
                $this->checkBrackets($stringArray);

                $this->value = explode(',', substr($stringArray, 1, -1));
            } else {
                $this->value = null;
            }

            // Need it because of Cyclomatic Complexity.
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
        return 'regex:/^(^(in)?|^(notIn)?)\[((\w)+,?){1,}\]$/';
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
     * Throw exception when value is not array and not null.
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    protected function checkValue(): void
    {
        if (!is_array($this->value) && !$this->value === null) {
            throw new InvalidArgumentException('ArrayParser: Value must be of type array');
        }
    }

    /**
     * Throws exception if there is no opening or closing brackets in string.
     *
     * @throws InvalidArgumentException
     *
     * @param string $array String representation of query param.
     *
     * @return void
     */
    private function checkBrackets(string $array): void
    {
        if (!starts_with($array, '[')) {
            throw new InvalidArgumentException('ArrayParser: Array has no open bracket ([)');
        }

        if (!ends_with($array, ']')) {
            throw new InvalidArgumentException('ArrayParser: Array has no closing bracket (])');
        }
    }
}
