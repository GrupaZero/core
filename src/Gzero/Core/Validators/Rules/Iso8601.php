<?php namespace Gzero\Core\Validators\Rules;

use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Validator;
use InvalidArgumentException;

class Iso8601 {

    /**
     * Tests against the ISO8601 format constraints.
     * Function signature for use by the Laravel framework.
     *
     * @param string    $attribute  attribute name
     * @param string    $value      attribute value
     * @param array     $parameters other parameters
     * @param Validator $validator  the current validator instance
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @return bool
     */
    public static function passes($attribute, $value, $parameters, $validator)
    {
        return self::test($value);
    }

    /**
     * Tests against the ISO8601 format constraints.
     *
     * @param string $value date time string
     *
     * @return bool
     */
    public static function test($value)
    {
        try {
            Carbon::createFromFormat(DATE_ISO8601, $value);

            $regex = '';
            $regex .=  '([1-9][0-9]{0,4})\-(1[012]|0[1-9])\-(3[01]|[12][0-9]|0[1-9])';
            $regex .=  'T(2[0-3]|[01][0-9])\:([0-5][0-9])\:([0-5][0-9])';
            $regex .=  '[\+\-](1[012]|0[0-9])[0134][05]';
            $result = (bool) preg_match('/^' . $regex . '$/', $value);

            return $result;
        } catch (Exception $ex) {
            return false;
        }
    }
}
