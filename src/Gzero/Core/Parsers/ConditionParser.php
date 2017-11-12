<?php namespace Gzero\Core\Parsers;

use Gzero\Core\Query\QueryBuilder;
use Illuminate\Http\Request;

interface ConditionParser {

    /**
     * @param string $name    Field name
     * @param array  $options Optional array of options
     */
    public function __construct(string $name, $options = []);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getOperation(): string;

    /**
     * @return mixed
     */
    public function getValue();

    /**
     * @return bool
     */
    public function wasApplied(): bool;

    /**
     * @param Request $request Request object
     *
     * @return void
     */
    public function parse(Request $request);

    /**
     * @return mixed
     */
    public function getValidationRule();

    /**
     * @param QueryBuilder $builder Query builder
     *
     * @return void
     */
    public function apply(QueryBuilder $builder);

}
