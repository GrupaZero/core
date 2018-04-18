<?php namespace Gzero\Core\Parsers;

use Carbon\Carbon;
use Gzero\Core\Query\QueryBuilder;
use Gzero\InvalidArgumentException;
use Illuminate\Http\Request;

/**
 * @TODO write custom Laravel validator
 * @TODO parse date format to DB format
 * @TODO we should always have two dates
 * @TODO human readable? e.g. -7days,+2days
 */
class DateTimeRangeParser implements ConditionParser {

    /** @var string */
    protected $name;

    /** @var string */
    protected $operation = 'between';

    /** @var array */
    protected $value;

    /** @var bool */
    protected $applied = false;

    /** @var array */
    protected $availableOperations = ['!'];

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
            throw new InvalidArgumentException('DateRangeParser: Name must be defined');
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
     * @throws InvalidArgumentException
     */
    public function parse(Request $request)
    {
        if (!$request->has($this->name)) {
            return;
        }

        $this->applied = true;

        $this->extractTokens($request->input($this->name));

        $this->value = [
            Carbon::parse($this->from)->setTimezone('UTC'),
            Carbon::parse($this->to)->setTimezone('UTC')
        ];
    }

    protected $from;
    protected $to;

    protected function extractTokens($value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('DateRangeParser: Value can\'t be empty');
        }

        $operation = substr($value, 0, 1);

        if ($operation === '!') {
            $this->operation = 'not between';
            $value           = substr($value, 1);
        }

        $dateTimes = explode(',', $value);

        $this->from = $dateTimes[0];
        $this->to   = $dateTimes[1];
    }

    /**
     * It returns validation rules for this type
     *
     * @return string
     */
    public function getValidationRule()
    {
        $iso8601Regex = "\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}";
        $regexRule = "regex:/^[!]?" . $iso8601Regex . "," . $iso8601Regex . "$/";

        return $regexRule;
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
}
