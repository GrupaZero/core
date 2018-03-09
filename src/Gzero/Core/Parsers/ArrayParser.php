<?php namespace Gzero\Core\Parsers;

use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

class ArrayParser implements ConditionParser {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation;

    /** @var mixed */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    protected $availableOperations = ['in', 'not in'];

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

            $this->value = explode(',', $value);
            $this->operation = 'in';

            if (substr($value, 0, 1) === '!') {
                $this->operation = 'not in';
                $this->value = explode(',', substr($value, 1));
            } elseif (empty($value)) {
                $this->value = null;
            }

            if (!is_array($this->value) && $this->value !== null) {
                throw new InvalidArgumentException('ArrayParser: Value must be of type array');
            }

            // Need it because of Cyclomatic Complexity.
            $this->postProcessing();
        }
    }

    /**
     * It returns validation rules for this type
     *
     * @return string
     */
    public function getValidationRule()
    {
        return 'regex:/^!?[a-z0-9,]+$/';
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
     * Post process value, e.g. change its type
     *
     * @return void
     */
    protected function postProcessing(): void
    {
        if (isset($this->value)) {
            $this->value = array_map(function ($item) {
                if (ctype_digit($item)) {
                    return intval($item);
                } else {
                    return $item;
                }
            }, $this->value);
        }
    }
}
