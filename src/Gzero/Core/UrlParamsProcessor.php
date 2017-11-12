<?php namespace Gzero\Core;

use Gzero\Core\Parsers\ConditionParser;
use Gzero\Core\Query\QueryBuilder;
use Illuminate\Contracts\Validation\Factory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UrlParamsProcessor {

    /** @var int */
    protected $page = 1;

    /** @var int */
    protected $perPage = null;

    /** @var array */
    protected $parsers = [];

    /** @var array */
    protected $sorts = [];

    /** @var null */
    protected $searchQuery = null;

    /** @var Factory */
    protected $validator;

    /** @var array */
    protected $rules = [
        'page'     => 'numeric',
        'per_page' => 'numeric',
        'sort'     => 'string',
        'q'        => 'string',
    ];

    /**
     * UrlParamsProcessor constructor.
     *
     * @param Factory $validator Validator factory
     */
    public function __construct(Factory $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Returns page number
     *
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returns page number
     *
     * @return int|null
     */
    public function getPerPage(): ?int
    {
        return $this->perPage;
    }

    /**
     * Returns sorts array
     *
     * @return array
     */
    public function getSorts(): array
    {
        return $this->sorts;
    }

    /**
     *  Returns parsers array
     *
     * @return array
     */
    public function getParsers(): array
    {
        return $this->parsers;
    }

    /**
     *  Returns search query string
     *
     * @return string
     */
    public function getSearchQuery(): string
    {
        return $this->searchQuery;
    }

    /**
     * @param ConditionParser $parser          Parser
     * @param string|null     $validationRules additional validation rules
     *
     * @return $this
     */
    public function addFilter(ConditionParser $parser, string $validationRules = null)
    {
        $this->parsers[] = $parser;
        if ($validationRules !== null) {
            $rules                           = (is_object($parser->getValidationRule())) ?
                [$validationRules, $parser->getValidationRule()] : $validationRules . '|' . $parser->getValidationRule();
            $this->rules[$parser->getName()] = $rules;
        } else {
            if (!empty($parser->getValidationRule())) {
                $this->rules[$parser->getName()] = [$parser->getValidationRule()];
            }
        }
        return $this;
    }

    /**
     * Process params
     *
     * @param Request $request Request object
     *
     * @return $this
     */
    public function process(Request $request)
    {
        $this->validate($request->all(), $this->rules);

        if ($request->has('q')) {
            $this->searchQuery = $request->get('q');
        }
        if ($request->has('sort')) {
            foreach (explode(',', $request->get('sort')) as $sort) {
                $this->processOrderByParams($sort);
            }
        }
        $this->processPageParams($request);

        foreach ($this->parsers as $parser) {
            $parser->parse($request);
        }

        return $this;
    }

    /**
     * Builds QueryBuilder instance based on parsed filters & sorts
     *
     * @return QueryBuilder
     */
    public function buildQueryBuilder()
    {
        $builder = new QueryBuilder();

        foreach ($this->parsers as $parser) {
            if ($parser->wasApplied()) {
                $parser->apply($builder);
            }
        }

        foreach ($this->sorts as $sort) {
            $builder->orderBy($sort[0], $sort[1]);
        }

        if (!empty($this->perPage)) {
            $builder->setPageSize($this->perPage);
        }

        if (!empty($this->page)) {
            $builder->setPage($this->page);
        }

        return $builder;
    }

    /**
     * Resets processor params to default values
     *
     * @return void
     */
    public function reset()
    {
        $this->page        = 1;
        $this->perPage     = null;
        $this->parsers     = [];
        $this->sorts       = [];
        $this->searchQuery = null;
        $this->rules       = [
            'page'     => 'numeric',
            'per_page' => 'numeric',
            'sort'     => 'string',
            'q'        => 'string',
        ];
    }

    /**
     * Validates
     *
     * @param array $data  Data to validate
     * @param array $rules Validation rules
     *
     * @throws ValidationException
     * @return $this
     */
    protected function validate(array $data, array $rules)
    {
        $validator = $this->validator->make($data, $rules);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        return $this;
    }

    /**
     * Process order by params
     *
     * @param string $sort Sort parameter
     *
     * @return void
     */
    protected function processOrderByParams($sort)
    {
        $direction     = (substr($sort, 0, 1) == '-') ? 'DESC' : 'ASC';
        $field         = (substr($sort, 0, 1) == '-') ? substr($sort, 1) : $sort;
        $this->sorts[] = [$field, $direction];
    }

    /**
     * Process page params
     *
     * @param Request $request Request object
     *
     * @return void
     */
    protected function processPageParams(Request $request)
    {
        if ($request->has('page') && is_numeric($request->get('page'))) {
            $this->page = $request->get('page');
        }
        if ($request->has('per_page') && is_numeric($request->get('per_page'))) {
            $this->perPage = $request->get('per_page');
        }
    }
}
